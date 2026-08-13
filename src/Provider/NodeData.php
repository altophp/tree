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

namespace Alto\Tree\Provider;

/**
 * Data Transfer Object for tree node information.
 *
 * This immutable object carries normalized node data from providers to TreeBuilder.
 * It includes the essential information needed to construct a TreeNode: path, type,
 * and optional metadata.
 *
 * @author Simon André <smn.andre@gmail.com>
 */
readonly class NodeData
{
    /**
     * @param string                    $path     The full path to the node
     * @param bool                      $isDir    Whether this node represents a directory
     * @param array<string, mixed>|null $metadata Optional metadata (size, mtime, permissions, etc.)
     */
    public function __construct(
        public string $path,
        public bool $isDir,
        public ?array $metadata = null,
    ) {}

    /**
     * Create a NodeData for a directory.
     *
     * @param array<string, mixed>|null $metadata
     */
    public static function directory(string $path, ?array $metadata = null): self
    {
        return new self($path, true, $metadata);
    }

    /**
     * Create a NodeData for a file.
     *
     * @param array<string, mixed>|null $metadata
     */
    public static function file(string $path, ?array $metadata = null): self
    {
        return new self($path, false, $metadata);
    }
}
