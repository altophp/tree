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

namespace Alto\Tree\Traverser;

use Alto\Tree\TreeNode;
use Alto\Tree\Visitor\VisitorInterface;

/**
 * @author Simon André <smn.andre@gmail.com>
 */
interface TraverserInterface
{
    /**
     * Add a visitor to the traverser.
     */
    public function addVisitor(VisitorInterface $visitor): self;

    /**
     * Traverse a tree node and its children.
     */
    public function traverse(TreeNode $node): void;
}
