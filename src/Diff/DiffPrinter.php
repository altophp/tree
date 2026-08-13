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

/**
 * Prints a visual representation of tree differences.
 *
 * Displays added nodes with '+' prefix and removed nodes with '-' prefix.
 *
 * @author Simon André <smn.andre@gmail.com>
 */
final class DiffPrinter
{
    /**
     * Print a diff result as a simple list of changes.
     *
     * @param DiffResult           $diff    The diff result to print
     * @param array<string, mixed> $options Print options
     *
     * @return string The formatted diff output
     */
    public function print(DiffResult $diff, array $options = []): string
    {
        $showUnchanged = $options['show_unchanged'] ?? false;
        $output = '';

        $removed = $diff->getRemoved();
        $added = $diff->getAdded();
        $unchanged = $diff->getUnchanged();

        ksort($removed);
        ksort($added);
        ksort($unchanged);

        foreach ($removed as $path => $node) {
            $output .= "- $path\n";
        }

        foreach ($added as $path => $node) {
            $output .= "+ $path\n";
        }

        if ($showUnchanged) {
            foreach ($unchanged as $path => $node) {
                $output .= "  $path\n";
            }
        }

        return $output;
    }

    /**
     * Print a compact diff summary.
     */
    public function printSummary(DiffResult $diff): string
    {
        $output = "Diff Summary\n";
        $output .= str_repeat('=', 40) . PHP_EOL;
        $output .= $diff->getDetailedSummary() . PHP_EOL;

        if ($diff->getAddedCount() > 0) {
            $output .= "\nAdded:\n";
            foreach ($diff->getAdded() as $node) {
                $type = $node->isDir ? '[DIR]' : '[FILE]';
                $output .= "  + $type {$node->path}\n";
            }
        }

        if ($diff->getRemovedCount() > 0) {
            $output .= "\nRemoved:\n";
            foreach ($diff->getRemoved() as $node) {
                $type = $node->isDir ? '[DIR]' : '[FILE]';
                $output .= "  - $type {$node->path}\n";
            }
        }

        return $output;
    }

    /**
     * Print diff in unified format (similar to git diff).
     */
    public function printUnified(DiffResult $diff, string $oldLabel = 'old', string $newLabel = 'new'): string
    {
        $output = "--- $oldLabel\n";
        $output .= "+++ $newLabel\n";
        $output .= $diff->getSummary() . "\n\n";

        foreach ($diff->getRemoved() as $node) {
            $output .= "- {$node->path}\n";
        }

        foreach ($diff->getAdded() as $node) {
            $output .= "+ {$node->path}\n";
        }

        return $output;
    }
}
