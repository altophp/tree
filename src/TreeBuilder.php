<?php

declare(strict_types=1);

/*
 * This file is part of the ALTO library.
 *
 * © 2026–present Simon André
 *
 * For full copyright and license information, please see
 * the LICENSE file distributed with this source code.
 */

namespace Alto\Tree;

use Alto\Tree\Provider\FileSystemProvider;
use Alto\Tree\Provider\GitProvider;
use Alto\Tree\Provider\PathsProvider;
use Alto\Tree\Provider\TreeSourceProviderInterface;

/**
 * Factory for building Tree instances from various sources.
 *
 * TreeBuilder uses the Provider pattern to support multiple data sources.
 * You can use the generic from() method with any provider, or use the
 * convenience methods (fromPaths, fromFilesystem, fromGit) for common cases.
 *
 * @author Simon André <smn.andre@gmail.com>
 */
final class TreeBuilder
{
    /**
     * Build a tree from any provider.
     *
     * This is the generic method that all convenience methods delegate to.
     * Use this when you have a custom provider or want explicit control.
     *
     * @param TreeSourceProviderInterface $provider The data source provider
     *
     * @return Tree The constructed tree
     */
    public static function from(TreeSourceProviderInterface $provider): Tree
    {
        $rootPath = $provider->getRootPath();
        $nodeDataList = $provider->getNodes();

        /** @var array<string, TreeNode> $nodes */
        $nodes = [];
        $root = new Tree($rootPath);
        $nodes[$rootPath] = $root;

        foreach ($nodeDataList as $nodeData) {
            if (!isset($nodes[$nodeData->path])) {
                $nodes[$nodeData->path] = new TreeNode(
                    $nodeData->path,
                    $nodeData->isDir,
                    $nodeData->metadata
                );
            }
        }

        foreach ($nodes as $path => $node) {
            if ($path === $rootPath) {
                continue;
            }

            $parent = dirname($path);

            if (isset($nodes[$parent])) {
                $nodes[$parent]->addChild($node);
            } elseif ('.' === $parent || '/' === $parent || '\\' === $parent || '' === $parent) {
                $root->addChild($node);
            }
        }

        return $root;
    }

    /**
     * Build a tree from an array of file paths.
     *
     * This is the simplest way to build a tree when you already have
     * a list of paths (e.g., from git ls-files, find command, etc.).
     *
     * @param array<string> $paths    Array of file/directory paths
     * @param string        $rootPath The root path for the tree (default: 'src')
     *
     * @return Tree The constructed tree
     */
    public static function fromPaths(array $paths, string $rootPath = 'src'): Tree
    {
        return self::from(new PathsProvider($paths, $rootPath));
    }

    /**
     * Build a tree by scanning the filesystem.
     *
     * Scans a directory and creates a tree from the actual files and directories.
     * Supports filtering, depth limits, and metadata extraction.
     *
     * @param string               $directory The directory to scan
     * @param array<string, mixed> $options   Configuration options:
     *                                        - max_depth: int - Maximum depth to scan (-1 for unlimited)
     *                                        - exclude: array<string> - Patterns to exclude
     *                                        - include_hidden: bool - Include hidden files (default: false)
     *                                        - with_metadata: bool - Extract file metadata (default: false)
     *                                        - follow_symlinks: bool - Follow symbolic links (default: false)
     *
     * @return Tree The constructed tree
     */
    public static function fromFilesystem(string $directory, array $options = []): Tree
    {
        return self::from(new FileSystemProvider($directory, $options));
    }

    /**
     * Build a tree from git repository files.
     *
     * Executes git commands to retrieve file lists and builds a tree.
     * Supports various git operations (ls-files, diff, etc.).
     *
     * @param string               $repositoryPath Path to the git repository
     * @param array<string, mixed> $options        Configuration options:
     *                                             - diff: string - Show diff between refs (e.g., 'main..feature')
     *                                             - staged_only: bool - Show only staged files
     *                                             - modified_only: bool - Show only modified files
     *                                             - commit: string - Show files from a specific commit
     *                                             - branch: string - Show files from a specific branch
     *
     * @return Tree The constructed tree
     */
    public static function fromGit(string $repositoryPath, array $options = []): Tree
    {
        return self::from(new GitProvider($repositoryPath, $options));
    }
}
