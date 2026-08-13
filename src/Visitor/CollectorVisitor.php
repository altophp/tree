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

namespace Alto\Tree\Visitor;

use Alto\Tree\TreeNode;

/**
 * @author Simon André <smn.andre@gmail.com>
 */
final class CollectorVisitor implements VisitorInterface
{
    /**
     * @var array<string>
     */ private array $files = [];

    /**
     * @var array<string>
     */ private array $directories = [];

    private int $totalFiles = 0;

    private int $totalDirectories = 0;

    public function visitNode(TreeNode $node, int $depth): void
    {
        if (!$node->isDir) {
            $this->files[] = $node->path;
            ++$this->totalFiles;
        }
    }

    public function enterDirectory(TreeNode $node, int $depth): void
    {
        $this->directories[] = $node->path;
        ++$this->totalDirectories;
    }

    public function leaveDirectory(TreeNode $node, int $depth): void
    {
        // Nothing specific to do when leaving a directory
    }

    /**
     * @return array<string>
     */
    public function getFiles(): array
    {
        return $this->files;
    }

    /**
     * @return array<string>
     */
    public function getDirectories(): array
    {
        return $this->directories;
    }

    public function getTotalFiles(): int
    {
        return $this->totalFiles;
    }

    public function getTotalDirectories(): int
    {
        return $this->totalDirectories;
    }

    public function getSummary(): string
    {
        return sprintf(
            'Found %d files and %d directories',
            $this->totalFiles,
            $this->totalDirectories,
        );
    }
}
