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

namespace Alto\Tree\Traverser;

use Alto\Tree\Tree;
use Alto\Tree\TreeBuilder;
use Alto\Tree\TreeNode;
use Alto\Tree\Visitor\FlattenVisitor;

/**
 * Flatten a tree to a list of paths and rebuild from paths.
 *
 * @author Simon André <smn.andre@gmail.com>
 */
final class TreeFlattener
{
    /**
     * Flatten a tree into an array of paths.
     *
     * @return array<string>
     */
    public static function flatten(TreeNode $tree): array
    {
        $traverser = new TreeTraverser();
        $visitor = new FlattenVisitor();

        $traverser->addVisitor($visitor);
        $traverser->traverse($tree);

        return $visitor->getPaths();
    }

    /**
     * Build a tree from a flat list of paths.
     *
     * @param array<string> $paths
     */
    public static function buildTree(array $paths, ?string $rootPath = null): Tree
    {
        if (null === $rootPath) {
            $rootPath = self::findCommonBasePath($paths);
        }

        return TreeBuilder::fromPaths($paths, $rootPath);
    }

    /**
     * Find the common base path for a set of paths.
     *
     * @param array<string> $paths
     */
    private static function findCommonBasePath(array $paths): string
    {
        if (empty($paths)) {
            return '';
        }

        $firstPath = reset($paths);
        $commonPath = dirname((string) $firstPath);

        foreach ($paths as $path) {
            while ($commonPath && !str_starts_with($path, $commonPath)) {
                $commonPath = dirname($commonPath);

                if ('.' === $commonPath || '/' === $commonPath) {
                    return '';
                }
            }
        }

        return $commonPath;
    }
}
