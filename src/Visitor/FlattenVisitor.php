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

namespace Alto\Tree\Visitor;

use Alto\Tree\TreeNode;

/**
 * Visitor to flatten a tree structure into a list of paths.
 *
 * @author Simon André <smn.andre@gmail.com>
 */
final class FlattenVisitor implements VisitorInterface
{
    /** @var array<string> */ private array $paths = [];

    public function visitNode(TreeNode $node, int $depth): void
    {
        $this->paths[] = $node->path;
    }

    public function enterDirectory(TreeNode $node, int $depth): void
    {
    }

    public function leaveDirectory(TreeNode $node, int $depth): void
    {
    }

    /**
     * Get the flattened list of paths.
     */
    /** @return array<string> */
    public function getPaths(): array
    {
        return $this->paths;
    }
}
