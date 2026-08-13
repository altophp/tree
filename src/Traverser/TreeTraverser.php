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
final class TreeTraverser implements TraverserInterface
{
    /**
     * @var VisitorInterface[]
     */
    private array $visitors = [];

    public function addVisitor(VisitorInterface $visitor): self
    {
        $this->visitors[] = $visitor;

        return $this;
    }

    public function traverse(TreeNode $node): void
    {
        $this->doTraverse($node, 0);
    }

    private function doTraverse(TreeNode $node, int $depth): void
    {
        foreach ($this->visitors as $visitor) {
            $visitor->visitNode($node, $depth);
        }

        if ($node->isDir && count($node->children) > 0) {
            foreach ($this->visitors as $visitor) {
                $visitor->enterDirectory($node, $depth);
            }

            foreach ($node->children as $child) {
                $this->doTraverse($child, $depth + 1);
            }

            foreach ($this->visitors as $visitor) {
                $visitor->leaveDirectory($node, $depth);
            }
        }
    }
}
