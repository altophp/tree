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
 * Provider that builds tree nodes by scanning the filesystem.
 *
 * This provider reads the actual filesystem and creates tree nodes from
 * directories and files. It supports filtering, depth limits, and metadata extraction.
 *
 * Example:
 * ```php
 * $provider = new FileSystemProvider('/path/to/project', [
 *     'max_depth' => 3,
 *     'exclude' => ['vendor', 'node_modules', '.git'],
 *     'include_hidden' => false,
 *     'with_metadata' => true,
 * ]);
 *
 * $tree = TreeBuilder::from($provider);
 * ```
 *
 * @author Simon André <smn.andre@gmail.com>
 */
final class FileSystemProvider implements TreeSourceProviderInterface
{
    private readonly string $directory;

    /**
     * @param string               $directory The directory to scan
     * @param array<string, mixed> $options   Configuration options
     */
    public function __construct(
        string $directory,
        private readonly array $options = [],
    ) {
        if (!is_dir($directory)) {
            throw new \InvalidArgumentException("Directory does not exist: $directory");
        }

        $this->directory = rtrim($directory, '/\\');
    }

    public function getRootPath(): string
    {
        return basename($this->directory);
    }

    /**
     * @return array<NodeData>
     */
    public function getNodes(): array
    {
        /** @var int $maxDepth */
        $maxDepth = $this->options['max_depth'] ?? -1;
        /** @var array<string> $exclude */
        $exclude = $this->options['exclude'] ?? [];
        $includeHidden = $this->options['include_hidden'] ?? false;
        $withMetadata = $this->options['with_metadata'] ?? false;
        $followSymlinks = $this->options['follow_symlinks'] ?? false;

        $nodes = [];
        $rootPath = $this->getRootPath();

        $flags = \RecursiveDirectoryIterator::SKIP_DOTS;
        if ($followSymlinks) {
            $flags |= \RecursiveDirectoryIterator::FOLLOW_SYMLINKS;
        }

        try {
            $iterator = new \RecursiveDirectoryIterator($this->directory, $flags);
            $iteratorMode = \RecursiveIteratorIterator::SELF_FIRST;

            if ($maxDepth >= 0) {
                $recursiveIterator = new \RecursiveIteratorIterator($iterator, $iteratorMode);
                $recursiveIterator->setMaxDepth($maxDepth);
            } else {
                $recursiveIterator = new \RecursiveIteratorIterator($iterator, $iteratorMode);
            }

            foreach ($recursiveIterator as $fileInfo) {
                /** @var \SplFileInfo $fileInfo */
                if (!$includeHidden && str_starts_with($fileInfo->getFilename(), '.')) {
                    continue;
                }

                $relativePath = str_replace($this->directory . '/', '', $fileInfo->getPathname());
                $relativePath = str_replace('\\\\', '/', $relativePath);

                if ($this->shouldExclude($relativePath, $exclude)) {
                    continue;
                }

                $nodePath = $rootPath . '/' . $relativePath;

                $metadata = null;
                if ($withMetadata) {
                    $metadata = [
                        'size' => $fileInfo->getSize(),
                        'mtime' => $fileInfo->getMTime(),
                        'permissions' => substr(sprintf('%o', $fileInfo->getPerms()), -4),
                        'is_readable' => $fileInfo->isReadable(),
                        'is_writable' => $fileInfo->isWritable(),
                    ];
                }

                $nodes[] = new NodeData(
                    $nodePath,
                    $fileInfo->isDir(),
                    $metadata,
                );
            }
        } catch (\UnexpectedValueException $e) {
            throw new \RuntimeException("Error scanning directory: {$e->getMessage()}", 0, $e);
        }

        return $nodes;
    }

    /**
     * Check if a path should be excluded based on patterns.
     *
     * @param array<string> $excludePatterns
     */
    private function shouldExclude(string $path, array $excludePatterns): bool
    {
        foreach ($excludePatterns as $pattern) {
            if (str_contains($path, $pattern)) {
                return true;
            }

            // Support glob-like patterns
            if (fnmatch($pattern, $path)) {
                return true;
            }
        }

        return false;
    }
}
