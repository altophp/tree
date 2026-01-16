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

namespace Alto\Tree\Provider;

/**
 * Interface for tree data providers.
 *
 * Providers are responsible for retrieving tree node data from various sources
 * (filesystem, git, arrays, etc.) and converting them into a normalized format
 * that TreeBuilder can consume.
 *
 * The provider pattern allows TreeBuilder to remain focused on tree construction
 * while delegating data retrieval to specialized implementations.
 *
 * @author Simon André <smn.andre@gmail.com>
 */
interface TreeSourceProviderInterface
{
    /**
     * Get the root path for the tree.
     *
     * @return string The root path that will be used for the Tree instance
     */
    public function getRootPath(): string;

    /**
     * Get all nodes that should be in the tree.
     *
     * @return array<NodeData> Array of NodeData objects representing the tree structure
     */
    public function getNodes(): array;
}
