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

namespace Alto\Tree\Printer;

use Alto\Tree\TreeNode;

/**
 * Enhanced tree printer with filtering, sorting, and formatting options.
 *
 * @author Simon André <smn.andre@gmail.com>
 */
final class TreePrinter implements TreePrinterInterface
{
    private PrinterOptions $options;
    private int $currentDepth = 0;

    /**
     * Print a tree with enhanced options.
     *
     * @param TreeNode             $tree    The tree to print
     * @param array<string, mixed> $options Printer options
     *
     * @return string The formatted tree output
     */
    public function print(TreeNode $tree, array $options = []): string
    {
        $this->options = PrinterOptions::fromArray($options);
        $this->currentDepth = 0;

        return $this->printNode($tree, '');
    }

    /**
     * Print a node and its children recursively.
     */
    private function printNode(TreeNode $node, string $indent): string
    {
        if (null !== $this->options->maxDepth && $this->currentDepth > $this->options->maxDepth) {
            return '';
        }

        $children = $this->filterAndSortChildren($node);
        $count = count($children);
        $result = '';

        foreach ($children as $i => $child) {
            $isLast = $i === $count - 1;
            $branch = $isLast ? '└── ' : '├── ';
            $childIndent = $indent . ($isLast ? '    ' : '│   ');

            $nodeLine = $this->formatNode($child);

            $result .= $indent . $branch . $nodeLine . PHP_EOL;

            if ($child->isDir && !empty($child->children)) {
                ++$this->currentDepth;
                $result .= $this->printNode($child, $childIndent);
                --$this->currentDepth;
            }
        }

        return $result;
    }

    /**
     * Filter and sort children based on options.
     *
     * @return array<TreeNode>
     */
    private function filterAndSortChildren(TreeNode $node): array
    {
        $children = array_values($node->children);

        $children = array_filter($children, function (TreeNode $child) {
            return $this->options->shouldDisplay($child->name, $child->isDir);
        });

        if (null !== $this->options->sortBy) {
            usort($children, function (TreeNode $a, TreeNode $b) {
                return match ($this->options->sortBy) {
                    'size' => $this->compareBySize($a, $b),
                    'date' => $this->compareByDate($a, $b),
                    'type' => $this->compareByType($a, $b),
                    default => $this->compareByName($a, $b),
                };
            });

            if ('desc' === $this->options->sortOrder) {
                $children = array_reverse($children);
            }
        }

        return array_values($children);
    }

    /**
     * Format a node with optional metadata and colors.
     */
    private function formatNode(TreeNode $node): string
    {
        $parts = [];

        $name = $node->name;
        if ($this->options->colors) {
            $name = $this->colorize($name, $node->isDir);
        }
        $parts[] = $name;

        if ($this->options->showSize && isset($node->metadata['size'])) {
            /** @var int $size */
            $size = $node->metadata['size'];
            $parts[] = '(' . $this->formatSize($size) . ')';
        }

        if ($this->options->showDate && isset($node->metadata['mtime'])) {
            /** @var int $mtime */
            $mtime = $node->metadata['mtime'];
            $parts[] = date('Y-m-d H:i', $mtime);
        }

        if ($this->options->showPermissions && isset($node->metadata['permissions'])) {
            /** @var string $permissions */
            $permissions = $node->metadata['permissions'];
            $parts[] = '[' . $permissions . ']';
        }

        return implode(' ', $parts);
    }

    /**
     * Colorize text for terminal output.
     */
    private function colorize(string $text, bool $isDir): string
    {
        if ($isDir) {
            return "\033[34m" . $text . "\033[0m"; // Blue for directories
        }

        return $text;
    }

    /**
     * Format file size in human-readable format.
     */
    private function formatSize(int $bytes): string
    {
        $units = ['B', 'KB', 'MB', 'GB'];
        $index = 0;
        $size = (float) $bytes;

        while ($size >= 1024 && $index < count($units) - 1) {
            $size /= 1024;
            ++$index;
        }

        return round($size, 2) . ' ' . $units[$index];
    }

    /**
     * Compare nodes by name.
     */
    private function compareByName(TreeNode $a, TreeNode $b): int
    {
        // Don't separate directories and files, just sort by name
        return strcasecmp($a->name, $b->name);
    }

    /**
     * Compare nodes by size.
     */
    private function compareBySize(TreeNode $a, TreeNode $b): int
    {
        $sizeA = $a->metadata['size'] ?? 0;
        $sizeB = $b->metadata['size'] ?? 0;

        return $sizeA <=> $sizeB;
    }

    /**
     * Compare nodes by modification date.
     */
    private function compareByDate(TreeNode $a, TreeNode $b): int
    {
        $dateA = $a->metadata['mtime'] ?? 0;
        $dateB = $b->metadata['mtime'] ?? 0;

        return $dateA <=> $dateB;
    }

    /**
     * Compare nodes by type (directory vs file).
     */
    private function compareByType(TreeNode $a, TreeNode $b): int
    {
        if ($a->isDir && !$b->isDir) {
            return -1;
        }
        if (!$a->isDir && $b->isDir) {
            return 1;
        }

        return strcasecmp($a->name, $b->name);
    }
}
