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

namespace Alto\Tree\Diff;

use Alto\Tree\TreeNode;

/**
 * Compares two tree structures and identifies differences.
 *
 * This class analyzes two trees and produces a DiffResult containing
 * nodes that were added, removed, or unchanged between versions.
 *
 * @author Simon André <smn.andre@gmail.com>
 */
final class TreeDiff
{
    /**
     * Compare two trees and return the differences.
     *
     * @param TreeNode $oldTree The original tree
     * @param TreeNode $newTree The new tree to compare against
     *
     * @return DiffResult The comparison result
     */
    public static function compare(TreeNode $oldTree, TreeNode $newTree): DiffResult
    {
        $oldPaths = self::flattenToPathMap($oldTree);
        $newPaths = self::flattenToPathMap($newTree);

        $added = array_diff_key($newPaths, $oldPaths);

        $removed = array_diff_key($oldPaths, $newPaths);

        $unchanged = array_intersect_key($oldPaths, $newPaths);

        return new DiffResult($added, $removed, $unchanged);
    }

    /**
     * Flatten a tree into a map of path => TreeNode.
     *
     * @return array<string, TreeNode>
     */
    private static function flattenToPathMap(TreeNode $tree): array
    {
        $map = [];
        self::collectNodes($tree, $map);

        return $map;
    }

    /**
     * Recursively collect all nodes from a tree.
     *
     * @param array<string, TreeNode> $map
     */
    private static function collectNodes(TreeNode $node, array &$map): void
    {
        foreach ($node->children as $child) {
            $map[$child->path] = $child;

            if ($child->isDir) {
                self::collectNodes($child, $map);
            }
        }
    }
}
