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

namespace Alto\Tree;

/**
 * Represents the root node of a tree structure.
 *
 * This class extends TreeNode and serves as the entry point for building
 * and manipulating tree structures that represent file/directory hierarchies.
 *
 * @author Simon André <smn.andre@gmail.com>
 */
final class Tree extends TreeNode implements \Stringable
{
    /**
     * Creates a new tree with the specified root path.
     *
     * @param string $path The root path of the tree
     */
    public function __construct(
        public string $path,
    ) {
        parent::__construct($path);
    }

    /**
     * Returns a string representation of the tree root.
     *
     * The output includes the path followed by an underline of equal signs.
     *
     * @return string The formatted tree root path
     */
    public function __toString(): string
    {
        return
            $this->path."\n"
            .str_repeat('=', strlen($this->path))."\n"
        ;
    }
}
