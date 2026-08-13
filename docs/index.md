# Alto Tree

Alto Tree builds, parses, traverses, prints, manipulates, and compares
hierarchies of files and directories.

```php
use Alto\Tree\Printer\TreePrinter;
use Alto\Tree\TreeBuilder;

$tree = TreeBuilder::fromPaths([
    'project/src/App.php',
    'project/tests/AppTest.php',
], 'project');

echo (new TreePrinter())->print($tree);
```

## Introduction

- [Installation](installation.md): install the package and verify the runtime.
- [Getting started](getting-started.md): build and print a first tree.

## Trees

- [Building](building.md): create trees from paths, directories, Git, or custom providers.
- [Parsing](parsing.md): read tree-shaped text back into objects.
- [Printing](printing.md): filter, sort, and format tree output.
- [Traversal](traversal.md): collect information or visit nodes with custom behavior.
- [Editing](editing.md): split, merge, append, flatten, and rebuild trees.
- [Comparing](comparing.md): find and print added, removed, and unchanged paths.
