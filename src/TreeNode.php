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

namespace Alto\Tree;

/**
 * Represents a node in a tree structure.
 *
 * Each node can represent either a file or a directory in a hierarchical structure.
 * Nodes maintain their path, name, type (file/directory), and children.
 *
 * @author Simon André <smn.andre@gmail.com>
 */
class TreeNode implements \Stringable
{
    /**
     * The base name of the node (file or directory name).
     */
    public string $name;

    /**
     * Whether this node represents a directory.
     */
    public bool $isDir;

    /**
     * Child nodes indexed by their names.
     *
     * @var array<string, TreeNode>
     */
    public array $children = [];

    /**
     * Optional metadata about this node (size, mtime, permissions, etc.).
     *
     * @var array<string, mixed>|null
     */
    public ?array $metadata = null;

    /**
     * Creates a new tree node.
     *
     * @param string                    $path     The full path to this node
     * @param bool                      $isDir    Whether this node represents a directory (default: true)
     * @param array<string, mixed>|null $metadata Optional metadata (default: null)
     */
    public function __construct(
        public string $path,
        bool $isDir = true,
        ?array $metadata = null,
    ) {
        $this->name = basename($path);
        $this->isDir = $isDir;
        $this->metadata = $metadata;
    }

    /**
     * Adds a child node to this node.
     *
     * @param TreeNode $child The child node to add
     */
    public function addChild(TreeNode $child): void
    {
        $this->children[$child->name] = $child;
    }

    /**
     * Returns a string representation of the node.
     *
     * For directories, includes an underline. For files, just the path.
     *
     * @return string The formatted node representation
     */
    public function __toString(): string
    {
        return
        $this->isDir
            ? $this->path . "\n"
            . str_repeat('=', strlen($this->path)) . "\n"
            : $this->path . "\n"
        ;
    }

    /**
     * Extracts a subtree starting from a specific path.
     *
     * This method allows you to split the tree and get a clone of a subtree
     * rooted at the specified path.
     *
     * @param string $path The path to extract (can be absolute or relative)
     *
     * @return TreeNode|null The cloned subtree, or null if the path doesn't exist
     */
    public function split(string $path): ?TreeNode
    {
        if (empty($path) || $path === $this->path) {
            return $this->cloneNode();
        }

        if (str_starts_with($path, $this->path . '/')) {
            $path = substr($path, strlen($this->path) + 1);
        }

        $segments = explode('/', trim($path, '/'));
        $currentNode = $this;

        foreach ($segments as $segment) {
            if (isset($currentNode->children[$segment])) {
                $currentNode = $currentNode->children[$segment];
            } else {
                return null;
            }
        }

        return $currentNode->cloneNode();
    }

    /**
     * Merges another node into this one.
     *
     * Recursively merges directories. If a child exists in both nodes,
     * directories are merged recursively while files are kept from this node.
     *
     * @param TreeNode $other The node to merge into this one
     *
     * @return TreeNode A new merged node
     */
    public function merge(TreeNode $other): TreeNode
    {
        $result = $this->cloneNode(false);

        foreach ($this->children as $name => $child) {
            $result->addChild($child->cloneNode());
        }

        $this->mergeNodeChildren($other, $result);

        return $result;
    }

    /**
     * Appends another node as a child of this node.
     *
     * The child node is cloned and optionally renamed before being added.
     *
     * @param TreeNode    $child     The node to append as a child
     * @param string|null $childName Optional new name for the child node
     *
     * @return TreeNode A new node with the child appended
     */
    public function append(TreeNode $child, ?string $childName = null): TreeNode
    {
        $result = $this->cloneNode(false);

        foreach ($this->children as $name => $existingChild) {
            $result->addChild($existingChild->cloneNode());
        }

        $newChild = $child->cloneNode();
        if (null !== $childName) {
            $newChild->path = $result->path . '/' . $childName;
            $newChild->name = $childName;
        }

        $result->addChild($newChild);

        return $result;
    }

    /**
     * Creates a deep clone of this node.
     *
     * @param bool $includeChildren Whether to clone child nodes (default: true)
     *
     * @return TreeNode The cloned node
     */
    protected function cloneNode(bool $includeChildren = true): TreeNode
    {
        $clone = new TreeNode($this->path, $this->isDir, $this->metadata);

        if ($includeChildren) {
            foreach ($this->children as $name => $child) {
                $clone->addChild($child->cloneNode());
            }
        }

        return $clone;
    }

    /**
     * Helper method to recursively merge children from one node to another.
     *
     * @param TreeNode $source The source node to merge from
     * @param TreeNode $target The target node to merge into
     */
    private function mergeNodeChildren(TreeNode $source, TreeNode $target): void
    {
        foreach ($source->children as $name => $sourceChild) {
            if (isset($target->children[$name])) {
                if ($sourceChild->isDir && $target->children[$name]->isDir) {
                    $this->mergeNodeChildren($sourceChild, $target->children[$name]);
                }
            } else {
                $target->addChild($sourceChild->cloneNode());
            }
        }
    }
}
