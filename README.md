# ALTO Tree

Build, parse, traverse, print, edit, and compare filesystem trees in PHP.

&nbsp; ![PHP Version](https://img.shields.io/badge/PHP-8.2%2B-00B7FF?logoColor=00B7FF&labelColor=050608)
&nbsp; ![CI](https://img.shields.io/github/actions/workflow/status/altophp/tree/CI.yml?branch=main&label=Tests&labelColor=050608&color=00B7FF)
&nbsp; [![Packagist](https://img.shields.io/packagist/v/alto/tree?label=Packagist&labelColor=050608&color=00B7FF)](https://packagist.org/packages/alto/tree)
&nbsp; ![License](https://img.shields.io/github/license/altophp/tree?label=License&labelColor=050608&color=00B7FF)
&nbsp; [![GitHub Sponsors](https://img.shields.io/github/sponsors/smnandre?logo=githubsponsors&logoColor=00B7FF&label=%20Sponsor&labelColor=050608&color=00B7FF)](https://github.com/sponsors/smnandre)

ALTO Tree turns paths, directories, Git repositories, and textual tree diagrams into one typed
model. The same tree can then be filtered, rendered, traversed, split, merged, or compared without
coupling the operation to its original source.

```php
use Alto\Tree\Printer\TreePrinter;
use Alto\Tree\TreeBuilder;

$tree = TreeBuilder::fromPaths([
    'project/src/App.php',
    'project/tests/AppTest.php',
], 'project');

echo (new TreePrinter())->print($tree);
// ├── src
// │   └── App.php
// └── tests
//     └── AppTest.php
```

The package has no PHP package runtime dependencies. It provides dedicated providers, visitors,
printers, and diff objects while keeping the core tree model small and fully typed.

## Installation

Install ALTO Tree with Composer:

```bash
composer require alto/tree
```

ALTO Tree requires PHP 8.2 or later and the Mbstring extension. Mbstring is available in most PHP
distributions but must be enabled.

## Quick Start

Build a tree from paths and inspect its nodes:

```php
use Alto\Tree\TreeBuilder;

$tree = TreeBuilder::fromPaths([
    'project/src/Controller/HomeController.php',
    'project/src/Entity/User.php',
], 'project');

echo $tree->children['src']->children['Entity']->children['User.php']->path;
```

The root is a `Tree`; every descendant is a `TreeNode` with its path, name, directory flag,
children, and optional filesystem metadata.

## Building Trees

Create trees from known paths, a directory, a Git repository, or a custom provider:

```php
$filesystem = TreeBuilder::fromFilesystem(__DIR__, [
    'max_depth' => 3,
    'exclude' => ['vendor', '.git'],
]);

$tracked = TreeBuilder::fromGit(__DIR__);
$modified = TreeBuilder::fromGit(__DIR__, ['modified_only' => true]);
```

Read [Building trees](docs/building.md) for every provider, filesystem metadata, Git modes, and
the custom provider contract.

## Parsing and Printing

`TreeParser` reads box-drawing trees, bullet lists, and plain indentation. `TreePrinter` renders
the model with depth, pattern, visibility, sorting, metadata, and terminal-color options.

```php
use Alto\Tree\Parser\TreeParser;
use Alto\Tree\Printer\TreePrinter;

$tree = (new TreeParser())->parse("project\n  src\n    App.php");
$output = (new TreePrinter())->print($tree, ['pattern' => '*.php']);
```

See [Parsing trees](docs/parsing.md) and [Printing trees](docs/printing.md).

## Traversing and Editing

Use visitors to collect or analyze nodes. Tree edits return cloned structures, leaving their
inputs unchanged.

```php
$subtree = $tree->split('project/src');
$merged = $tree->merge($anotherTree);
$paths = Alto\Tree\Traverser\TreeFlattener::flatten($merged);
```

See [Traversing trees](docs/traversal.md) and [Editing trees](docs/editing.md).

## Comparing Trees

Classify paths as added, removed, or unchanged, then inspect or print the result:

```php
use Alto\Tree\Diff\TreeDiff;

$diff = TreeDiff::compare($before, $after);

echo $diff->getSummary();
```

Read [Comparing trees](docs/comparing.md) for result accessors and output formats. The
[complete guide](docs/index.md) links every topic.

## Contributing

Contributions of all kinds are welcome. Visit the
[project on GitHub](https://github.com/altophp/tree) to
[report a bug](https://github.com/altophp/tree/issues/new),
[suggest a feature](https://github.com/altophp/tree/issues/new), or
[open a pull request](https://github.com/altophp/tree/pulls).

Before submitting code, run:

```bash
# Runs PHP CS Fixer, PHPStan, and PHPUnit
composer qa
```

Changes to public behavior should include tests and documentation.

## Support

ALTO Tree is open source. You can support its continued development through
[GitHub Sponsors](https://github.com/sponsors/smnandre).

Sharing this package with others or
[starring it on GitHub](https://github.com/altophp/tree) is also much
appreciated.

## License

ALTO Tree is released by [ALTO PHP](https://altophp.com) under the
[MIT License](LICENSE).
