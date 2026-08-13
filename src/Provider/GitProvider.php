<?php

declare(strict_types=1);

/*
 * This file is part of the ALTO library.
 *
 * © 2026-present Simon André
 *
 * For full copyright and license information, please see
 * the LICENSE file distributed with this source code.
 */

namespace Alto\Tree\Provider;

/**
 * Provider that builds tree nodes from git repository files.
 *
 * This provider executes git commands to retrieve file lists and builds
 * tree nodes from the results. It supports various git operations like
 * listing tracked files, showing diffs, or getting files from specific commits.
 *
 * Example:
 * ```php
 * // List all tracked files
 * $provider = new GitProvider('/path/to/repo');
 *
 * // List files modified in a branch
 * $provider = new GitProvider('/path/to/repo', [
 *     'diff' => 'main..feature',
 * ]);
 *
 * // List staged files
 * $provider = new GitProvider('/path/to/repo', [
 *     'staged_only' => true,
 * ]);
 * ```
 *
 * @author Simon André <smn.andre@gmail.com>
 */
final class GitProvider implements TreeSourceProviderInterface
{
    private readonly string $repositoryPath;

    /**
     * @param string               $repositoryPath Path to the git repository
     * @param array<string, mixed> $options        Configuration options
     */
    public function __construct(
        string $repositoryPath,
        private readonly array $options = [],
    ) {
        if (!is_dir($repositoryPath)) {
            throw new \InvalidArgumentException("Repository path does not exist: $repositoryPath");
        }

        if (!is_dir($repositoryPath . '/.git')) {
            throw new \InvalidArgumentException("Not a git repository: $repositoryPath");
        }

        $this->repositoryPath = rtrim($repositoryPath, '/\\');
    }

    public function getRootPath(): string
    {
        return basename($this->repositoryPath);
    }

    /**
     * @return array<NodeData>
     */
    public function getNodes(): array
    {
        $paths = $this->getGitPaths();
        $nodes = [];
        $processedPaths = [];
        $rootPath = $this->getRootPath();

        foreach ($paths as $path) {
            $segments = explode('/', trim($path, '/'));
            $accum = '';

            foreach ($segments as $i => $segment) {
                $accum = '' === $accum ? $segment : "$accum/$segment";
                $isLast = $i === array_key_last($segments);
                $isDir = !$isLast || !str_contains($segment, '.');

                $fullPath = $rootPath . '/' . $accum;

                if (!isset($processedPaths[$fullPath])) {
                    $nodes[] = new NodeData($fullPath, $isDir);
                    $processedPaths[$fullPath] = true;
                }
            }
        }

        return $nodes;
    }

    /**
     * Execute git command and get list of files.
     *
     * @return array<string>
     */
    private function getGitPaths(): array
    {
        $command = $this->buildGitCommand();

        $output = [];
        $returnCode = 0;

        exec("cd {$this->repositoryPath} && $command 2>&1", $output, $returnCode);

        if (0 !== $returnCode) {
            throw new \RuntimeException('Git command failed: ' . implode("\n", $output));
        }

        return array_filter($output, fn($line) => !empty(trim($line)));
    }

    /**
     * Build the appropriate git command based on options.
     */
    private function buildGitCommand(): string
    {
        if (isset($this->options['diff'])) {
            /** @var string $diffRef */
            $diffRef = $this->options['diff'];
            $diff = escapeshellarg($diffRef);

            return "git diff --name-only $diff";
        }

        if ($this->options['staged_only'] ?? false) {
            return 'git diff --cached --name-only';
        }

        if ($this->options['modified_only'] ?? false) {
            return 'git diff --name-only';
        }

        if (isset($this->options['commit'])) {
            /** @var string $commitRef */
            $commitRef = $this->options['commit'];
            $commit = escapeshellarg($commitRef);

            return "git ls-tree -r --name-only $commit";
        }

        if (isset($this->options['branch'])) {
            /** @var string $branchRef */
            $branchRef = $this->options['branch'];
            $branch = escapeshellarg($branchRef);

            return "git ls-tree -r --name-only $branch";
        }

        return 'git ls-files';
    }
}
