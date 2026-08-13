# Editing trees

Tree operations return cloned structures and leave their inputs unchanged.

## Extract a subtree

```php
$src = $tree->split('project/src');

if (null === $src) {
    throw new RuntimeException('Subtree not found.');
}
```

`split()` accepts a path relative to the current node or prefixed with its root
path. It returns `null` when the path does not exist.

## Merge and append

```php
use Alto\Tree\TreeBuilder;

$tests = TreeBuilder::fromPaths(['project/tests/AppTest.php'], 'project');

$merged = TreeBuilder::fromPaths(['project/src/App.php'], 'project')
    ->merge($tests);
$withDocs = $merged->append(
    TreeBuilder::fromPaths(['docs/index.md'], 'docs'),
    'documentation',
);
```

Merge combines directory children recursively. When the same file exists in
both inputs, the receiver's node wins. Append adds a cloned child and may
rename its root.

## Flatten and rebuild

```php
use Alto\Tree\Traverser\TreeFlattener;

$paths = TreeFlattener::flatten($tree);
$rebuilt = TreeFlattener::buildTree($paths, 'project');
```

Flattening includes the root and follows traversal order. Supply the root when
rebuilding if automatic common-path inference would be ambiguous.
