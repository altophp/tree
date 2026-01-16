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

use Alto\Tree\Tree;
use Alto\Tree\TreeNode;

/**
 * @author Simon André <smn.andre@gmail.com>
 */
interface VisitorInterface
{
    /**
     * Called when a tree node is visited.
     */
    public function visitNode(TreeNode $node, int $depth): void;

    /**
     * Called when entering a directory node before visiting its children.
     */
    public function enterDirectory(TreeNode $node, int $depth): void;

    /**
     * Called when leaving a directory node after visiting its children.
     */
    public function leaveDirectory(TreeNode $node, int $depth): void;
}
