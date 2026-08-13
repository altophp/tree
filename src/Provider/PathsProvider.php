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
 * Provider that builds tree nodes from an array of file paths.
 *
 * This is the simplest provider and is useful when you already have
 * a list of paths (e.g., from git ls-files, find command, or any other source).
 *
 * Example:
 * ```php
 * $provider = new PathsProvider([
 *     'src/Controller/HomeController.php',
 *     'src/Model/User.php',
 * ], 'project');
 *
 * $tree = TreeBuilder::from($provider);
 * ```
 *
 * @author Simon André <smn.andre@gmail.com>
 */
readonly class PathsProvider implements TreeSourceProviderInterface
{
    /**
     * @param array<string> $paths    Array of file/directory paths
     * @param string        $rootPath The root path for the tree
     */
    public function __construct(
        private array $paths,
        private string $rootPath = 'src',
    ) {}

    public function getRootPath(): string
    {
        return $this->rootPath;
    }

    /**
     * @return array<NodeData>
     */
    public function getNodes(): array
    {
        $nodes = [];
        $processedPaths = [];

        foreach ($this->paths as $path) {
            $segments = explode('/', trim($path, '/'));
            $accum = '';

            foreach ($segments as $i => $segment) {
                $accum = '' === $accum ? $segment : "$accum/$segment";
                $isLast = $i === array_key_last($segments);
                $isDir = !$isLast || !str_contains($segment, '.');

                if (!isset($processedPaths[$accum])) {
                    $nodes[] = new NodeData($accum, $isDir);
                    $processedPaths[$accum] = true;
                }
            }
        }

        return $nodes;
    }
}
