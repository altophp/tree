# Parsing trees

`TreeParser` reads common textual tree formats and returns a `Tree`.

```php
use Alto\Tree\Parser\TreeParser;

$input = <<<'TREE'
project
├── src
│   └── App.php
└── tests
    └── AppTest.php
TREE;

$tree = (new TreeParser())->parse($input);

echo $tree->children['src']->children['App.php']->path;
```

The parser also accepts `*` or `-` lists and plain indentation:

```text
project
  src
    App.php
  tests
    AppTest.php
```

The first line becomes the root path. Empty lines are ignored. Indentation
defines parent-child relationships.

Because the text carries no explicit node type, a name containing a dot is
treated as a file and another name as a directory. Use [Building](building.md)
with `NodeData` when extensionless files or dotted directories must be typed
exactly.
