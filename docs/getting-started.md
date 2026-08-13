# Getting started

Build a tree from known paths, then print its hierarchy.

```php
use Alto\Tree\Printer\TreePrinter;
use Alto\Tree\TreeBuilder;

$tree = TreeBuilder::fromPaths([
    'project/src/Controller/HomeController.php',
    'project/src/Entity/User.php',
    'project/tests/HomeControllerTest.php',
], 'project');

echo (new TreePrinter())->print($tree);
```

The printer returns:

```text
├── src
│   ├── Controller
│   │   └── HomeController.php
│   └── Entity
│       └── User.php
└── tests
    └── HomeControllerTest.php
```

The root is a `Tree`; every other entry is a `TreeNode`. Nodes expose their
full `path`, basename `name`, directory flag, children keyed by name, and
optional metadata.

Continue with [Building](building.md) for other sources or [Printing](printing.md)
for filters and metadata.
