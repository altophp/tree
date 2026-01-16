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

namespace Alto\Tree\Diff;

use Alto\Tree\TreeNode;

/**
 * Represents the result of comparing two trees.
 *
 * Contains collections of nodes that were added, removed, or remain unchanged
 * between the old and new tree versions.
 *
 * @author Simon André <smn.andre@gmail.com>
 */
readonly class DiffResult
{
    /**
     * @param array<string, TreeNode> $added     Nodes that exist in new tree but not in old
     * @param array<string, TreeNode> $removed   Nodes that exist in old tree but not in new
     * @param array<string, TreeNode> $unchanged Nodes that exist in both trees
     */
    public function __construct(
        private array $added = [],
        private array $removed = [],
        private array $unchanged = [],
    ) {
    }

    /**
     * Get nodes that were added in the new tree.
     *
     * @return array<string, TreeNode>
     */
    public function getAdded(): array
    {
        return $this->added;
    }

    /**
     * Get nodes that were removed from the old tree.
     *
     * @return array<string, TreeNode>
     */
    public function getRemoved(): array
    {
        return $this->removed;
    }

    /**
     * Get nodes that are unchanged between trees.
     *
     * @return array<string, TreeNode>
     */
    public function getUnchanged(): array
    {
        return $this->unchanged;
    }

    /**
     * Get the total count of added nodes.
     */
    public function getAddedCount(): int
    {
        return count($this->added);
    }

    /**
     * Get the total count of removed nodes.
     */
    public function getRemovedCount(): int
    {
        return count($this->removed);
    }

    /**
     * Get the total count of unchanged nodes.
     */
    public function getUnchangedCount(): int
    {
        return count($this->unchanged);
    }

    /**
     * Check if there are any changes between the trees.
     */
    public function hasChanges(): bool
    {
        return $this->getAddedCount() > 0 || $this->getRemovedCount() > 0;
    }

    /**
     * Get a summary of the diff.
     *
     * @return string Summary in format: "+5 -3" or "No changes"
     */
    public function getSummary(): string
    {
        if (!$this->hasChanges()) {
            return 'No changes';
        }

        $parts = [];

        if ($this->getAddedCount() > 0) {
            $parts[] = '+'.$this->getAddedCount();
        }

        if ($this->getRemovedCount() > 0) {
            $parts[] = '-'.$this->getRemovedCount();
        }

        return implode(' ', $parts);
    }

    /**
     * Get a detailed summary with file and directory counts.
     */
    public function getDetailedSummary(): string
    {
        $addedFiles = count(array_filter($this->added, fn ($n) => !$n->isDir));
        $addedDirs = count(array_filter($this->added, fn ($n) => $n->isDir));
        $removedFiles = count(array_filter($this->removed, fn ($n) => !$n->isDir));
        $removedDirs = count(array_filter($this->removed, fn ($n) => $n->isDir));

        if (!$this->hasChanges()) {
            return 'No changes';
        }

        $parts = [];

        if ($addedFiles > 0 || $addedDirs > 0) {
            $added = [];
            if ($addedFiles > 0) {
                $added[] = "$addedFiles ".(1 === $addedFiles ? 'file' : 'files');
            }
            if ($addedDirs > 0) {
                $added[] = "$addedDirs ".(1 === $addedDirs ? 'directory' : 'directories');
            }
            $parts[] = 'Added: '.implode(', ', $added);
        }

        if ($removedFiles > 0 || $removedDirs > 0) {
            $removed = [];
            if ($removedFiles > 0) {
                $removed[] = "$removedFiles ".(1 === $removedFiles ? 'file' : 'files');
            }
            if ($removedDirs > 0) {
                $removed[] = "$removedDirs ".(1 === $removedDirs ? 'directory' : 'directories');
            }
            $parts[] = 'Removed: '.implode(', ', $removed);
        }

        return implode(' | ', $parts);
    }
}
