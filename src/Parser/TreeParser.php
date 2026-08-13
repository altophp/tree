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

namespace Alto\Tree\Parser;

use Alto\Tree\Tree;
use Alto\Tree\TreeNode;

/**
 * @author Simon André <smn.andre@gmail.com>
 */
final class TreeParser implements TreeParserInterface
{
    public function parse(string $tree): Tree
    {
        $lines = explode(PHP_EOL, $tree);

        // Extract root path from the first line
        $rootPath = trim($lines[0]);
        $root = new Tree($rootPath);

        if (count($lines) <= 1) {
            return $root;
        }

        $pathStack = [[$root, 0]];
        $currentIndentLevel = 0;

        // Process each line after the root
        for ($i = 1; $i < count($lines); ++$i) {
            $line = $lines[$i];

            // Skip empty lines
            if ('' === trim($line)) {
                continue;
            }

            // Calculate indent level
            $indentLevel = $this->getIndentLevel($line);

            // Extract node name from the line
            $name = $this->extractNodeName($line);
            if (empty($name)) {
                continue;
            }

            $isFile = str_contains($name, '.');

            // Build full path by appending to parent's path
            $parentNode = $this->findParentNode($pathStack, $indentLevel);
            $fullPath = $parentNode->path . '/' . $name;

            // Create the node
            $node = new TreeNode($fullPath, !$isFile);

            // Add to parent
            $parentNode->addChild($node);

            // Manage stack
            if ($indentLevel > $currentIndentLevel) {
                // Push to stack if we're going deeper
                $pathStack[] = [$node, $indentLevel];
            } else {
                // Pop from stack and add new node if we're at same or less indentation
                while (count($pathStack) > 1 && $pathStack[count($pathStack) - 1][1] >= $indentLevel) {
                    array_pop($pathStack);
                }
                $pathStack[] = [$node, $indentLevel];
            }

            $currentIndentLevel = $indentLevel;
        }

        return $root;
    }

    /**
     * Calculates the indentation level based on various formats.
     */
    private function getIndentLevel(string $line): int
    {
        // Count leading spaces before any character
        preg_match('/^(\s*)/', $line, $matches);
        $indent = $matches[1] ?? '';

        // For bullet and dashed lists, each level is typically 2 spaces
        if (preg_match('/^\s*[\*\-]/', $line)) {
            return intdiv(mb_strlen($indent), 2);
        }

        // For tree format with branches, each level is typically 4 characters
        if (preg_match('/^([ │├└]*)/', $line, $matches)) {
            return intdiv(mb_strlen($matches[1]), 4);
        }

        // For simple indentation (no special characters)
        return intdiv(mb_strlen($indent), 2);
    }

    /**
     * Extracts the node name from a line, supporting multiple formats.
     */
    private function extractNodeName(string $line): string
    {
        // Remove leading whitespace
        $line = ltrim($line);

        // Handle bullet and dashed list formats (* and -)
        if ('*' === substr($line, 0, 1) || '-' === substr($line, 0, 1)) {
            $line = ltrim(substr($line, 1));
        }

        // Handle tree branch format
        $line = (string) preg_replace('/^[│├└─\s]+/', '', $line);

        return trim($line);
    }

    /**
     * Finds the parent node for the current indentation level.
     *
     * @param array<int, array{TreeNode, int}> $stack The stack of nodes with their indentation levels
     * @param int                              $level The current indentation level
     *
     * @return TreeNode The parent node
     */
    private function findParentNode(array $stack, int $level): TreeNode
    {
        for ($i = count($stack) - 1; $i >= 0; --$i) {
            [$node, $nodeLevel] = $stack[$i];
            if ($nodeLevel < $level) {
                return $node;
            }
        }

        // If no appropriate parent found, return the root
        [$rootNode] = $stack[0];

        return $rootNode;
    }
}
