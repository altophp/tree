# Alto \ Tree

A modern PHP library for building, parsing, traversing, and printing tree structures representing file and directory hierarchies.

---

&nbsp; [![PHP Version](https://img.shields.io/badge/PHP-8.2+-ffefdf?logoColor=white&labelColor=000)](https://github.com/PhpAlto/tree)
&nbsp; [![CI](https://img.shields.io/github/actions/workflow/status/PhpAlto/tree/CI.yml?branch=main&label=Tests&logoColor=white&logoSize=auto&labelColor=000&color=ffefdf)](https://github.com/PhpAlto/tree/actions)
&nbsp; [![Packagist Version](https://img.shields.io/packagist/v/alto/tree?label=Stable&logoColor=white&logoSize=auto&labelColor=000&color=ffefdf)](https://packagist.org/packages/alto/tree)
&nbsp; [![PHP Version](https://img.shields.io/badge/PHPUnit-100%25-ffefdf?logoColor=white&labelColor=000)](https://github.com/PhpAlto/tree)
&nbsp; [![PHP Version](https://img.shields.io/badge/PHPStan-LVL%2010-ffefdf?logoColor=white&labelColor=000)](https://github.com/PhpAlto/tree)
&nbsp; [![License](https://img.shields.io/github/license/PhpAlto/tree?label=License&logoColor=white&logoSize=auto&labelColor=000&color=ffefdf)](./LICENSE)


## Why Alto Tree?

Alto Tree makes it easy to work with hierarchical file structures in PHP. Whether you need to:

- Visualize directory structures in your CLI tools
- Parse output from the `tree` command
- Analyze project organization
- Generate documentation with file trees
- Compare or merge directory structures

Alto Tree provides a clean, type-safe API with comprehensive documentation and examples.

## Features

- **Build Trees**: Create tree structures from arrays of file/directory paths
- **Parse Trees**: Parse string representations of trees in multiple formats (ASCII art, bullets, indented)
- **Print Trees**: Generate beautiful ASCII art representations of tree structures
- **Traverse Trees**: Use the visitor pattern to traverse and analyze tree structures
- **Manipulate Trees**: Split, merge, and append operations on tree nodes
- **Compare Trees**: Diff two trees to identify added, removed, and unchanged nodes
- **Type-Safe**: Fully typed with PHP 8.2+ features and strict types
- **Well-Tested**: Comprehensive test coverage with PHPUnit
- **Modern**: PSR-12 compliant, uses modern PHP features

## Requirements

- PHP 8.2 or higher

## Installation

Install via Composer:

```bash
composer require alto/tree
```

## Quick Start

### Basic Example

```php
use Alto\Tree\TreeBuilder;
use Alto\Tree\Printer\TreePrinter;

// Build a tree from paths
$paths = ['src/Controller/HomeController.php', 'src/Entity/User.php'];
$tree = TreeBuilder::fromPaths($paths, 'my-app');

// Print the tree
$printer = new TreePrinter();
echo $printer->print($tree);
```

### Build from Filesystem

```php
// Scan actual filesystem
$tree = TreeBuilder::fromFilesystem('/path/to/project', [
    'max_depth' => 3,
    'exclude' => ['vendor', 'node_modules'],
]);
```

### Build from Git Repository

```php
// Get git-tracked files
$tree = TreeBuilder::fromGit('/path/to/repo');

// Or get only modified files
$tree = TreeBuilder::fromGit('/path/to/repo', [
    'modified_only' => true,
]);
```

For more examples, see the [examples](_dev/examples/) directory and [usage guide](_dev/docs/GUIDE.md).

## Usage

### Building Trees

Alto Tree uses the **Provider Pattern** to support multiple data sources.

#### From Array of Paths

```php
use Alto\Tree\TreeBuilder;

$paths = [
    'src/Controller/HomeController.php',
    'src/Controller/UserController.php',
    'src/Entity/User.php',
];

$tree = TreeBuilder::fromPaths($paths, 'src');
```

#### From Filesystem

Scan actual files and directories:

```php
$tree = TreeBuilder::fromFilesystem('/path/to/project', [
    'max_depth' => 3,                    // Limit depth (default: unlimited)
    'exclude' => ['vendor', '.git'],     // Exclude patterns
    'include_hidden' => false,           // Include hidden files (default: false)
    'with_metadata' => true,             // Include file metadata (default: false)
]);
```

#### From Git Repository

Build tree from git-tracked files:

```php
// All tracked files
$tree = TreeBuilder::fromGit('/path/to/repo');

// Only modified files
$tree = TreeBuilder::fromGit('/path/to/repo', [
    'modified_only' => true,
]);

// Diff between branches
$tree = TreeBuilder::fromGit('/path/to/repo', [
    'diff' => 'main..feature',
]);

// Files from specific commit
$tree = TreeBuilder::fromGit('/path/to/repo', [
    'commit' => 'abc123',
]);
```

#### Custom Providers

Create your own provider for any data source:

```php
use Alto\Tree\Provider\TreeSourceProviderInterface;
use Alto\Tree\Provider\NodeData;

class ComposerProvider implements TreeSourceProviderInterface
{
    public function getRootPath(): string { return 'dependencies'; }

    public function getNodes(): array
    {
        // Parse composer.lock, return NodeData objects
        return [
            new NodeData('vendor/package', true),
            new NodeData('vendor/package/src/File.php', false),
        ];
    }
}

$tree = TreeBuilder::from(new ComposerProvider());
```

### Parsing a Tree from String

Parse a string representation of a tree back into a `Tree` object:

```php
use Alto\Tree\Parser\TreeParser;

$input = <<<EOT
src
├── Controller
│   ├── HomeController.php
│   └── UserController.php
└── Entity
    └── User.php
EOT;

$parser = new TreeParser();
$tree = $parser->parse($input);
```

The parser supports multiple formats:

#### ASCII Tree Format (with box-drawing characters)
```
src
├── Controller
│   └── HomeController.php
└── Entity
    └── User.php
```

#### Bullet List Format
```
src
* Controller
  * HomeController.php
* Entity
  * User.php
```

#### Dash List Format
```
src
- Controller
  - HomeController.php
- Entity
  - User.php
```

#### Simple Indentation (2 or 4 spaces)
```
src
  Controller
    HomeController.php
  Entity
    User.php
```

### Traversing a Tree

Use the visitor pattern to traverse and analyze tree structures:

```php
use Alto\Tree\Traverser\TreeTraverser;
use Alto\Tree\Visitor\CollectorVisitor;

$traverser = new TreeTraverser();
$collector = new CollectorVisitor();

$traverser->addVisitor($collector);
$traverser->traverse($tree);

echo $collector->getSummary();
// Output: Found 4 files and 3 directories

$files = $collector->getFiles();
$directories = $collector->getDirectories();
```

### Flattening a Tree

Convert a tree structure into a flat array of paths:

```php
use Alto\Tree\TreeFlattener;

$paths = TreeFlattener::flatten($tree);
// Returns: ['src/Controller', 'src/Controller/HomeController.php', ...]

// Rebuild a tree from paths
$newTree = TreeFlattener::buildTree($paths);
```

### Splitting a Tree

Extract a subtree from a specific path:

```php
$tree = TreeBuilder::fromPaths([
    'src/Controller/HomeController.php',
    'src/Entity/User.php'
]);

// Extract just the Controller subtree
$controllerTree = $tree->split('src/Controller');
```

### Merging Trees

Combine two trees into one:

```php
$tree1 = TreeBuilder::fromPaths(['src/Controller/HomeController.php']);
$tree2 = TreeBuilder::fromPaths(['src/Entity/User.php']);

$merged = $tree1->merge($tree2);
// Now contains both Controller and Entity directories
```

### Appending Nodes

Add a node as a child of another node:

```php
$mainTree = TreeBuilder::fromPaths(['src/Controller/HomeController.php']);
$extraNode = TreeBuilder::fromPaths(['config/app.php']);

$result = $mainTree->append($extraNode, 'config');
// Adds the config tree as a child of mainTree
```

### Comparing Trees (Diff)

Compare two trees to identify changes:

```php
use Alto\Tree\Diff\TreeDiff;
use Alto\Tree\Diff\DiffPrinter;

$oldTree = TreeBuilder::fromPaths(['project/src/App.php']);
$newTree = TreeBuilder::fromPaths([
    'project/src/App.php',
    'project/src/Router.php',  // Added
]);

$diff = TreeDiff::compare($oldTree, $newTree);

echo $diff->getSummary();  // "+1"
echo $diff->getDetailedSummary();  // "Added: 1 file"

// Print visual diff
$printer = new DiffPrinter();
echo $printer->print($diff);

// Get added/removed nodes
$added = $diff->getAdded();
$removed = $diff->getRemoved();
$unchanged = $diff->getUnchanged();
```

## API Reference

### Core Classes

#### `Tree`
**Final class** representing the root of a tree structure. Extends `TreeNode`.

#### `TreeNode`
Represents a node (file or directory) in the tree.

**Properties:**
- `string $path` - Full path to the node
- `string $name` - Base name (file/directory name)
- `bool $isDir` - Whether this is a directory
- `array<string, TreeNode> $children` - Child nodes
- `?array $metadata` - Optional metadata (size, mtime, permissions, etc.)

**Metadata:**
When using `with_metadata: true` option with FileSystemProvider, the metadata array contains:
- `size` (int) - File size in bytes
- `mtime` (int) - Last modification timestamp
- `permissions` (string) - Unix permissions (e.g., "0755")
- `is_readable` (bool) - Whether file is readable
- `is_writable` (bool) - Whether file is writable

**Methods:**
- `addChild(TreeNode $child): void` - Add a child node
- `split(string $path): ?TreeNode` - Extract a subtree
- `merge(TreeNode $other): TreeNode` - Merge with another node
- `append(TreeNode $child, ?string $childName = null): TreeNode` - Append a child node

#### `TreeBuilder`
**Final class** factory for building trees from paths.

**Methods:**
- `static fromPaths(array $paths, string $rootPath = 'src'): Tree` - Build a tree from array of paths

### Parsing

#### `TreeParser`
**Final class** that parses string representations into tree structures.

**Methods:**
- `parse(string $tree): Tree` - Parse a tree string into a Tree object

### Printing

#### `TreePrinter`
**Final class** that generates ASCII art representation of trees with extensive formatting and filtering options.

**Methods:**
- `print(TreeNode $tree, array $options = []): string` - Generate tree visualization

**Printer Options:**

*Filtering:*
- `show_hidden: bool` - Show/hide hidden files (default: true)
- `files_only: bool` - Show only files, exclude directories (default: false)
- `dirs_only: bool` - Show only directories, exclude files (default: false)
- `pattern: string` - Filter by shell-style pattern compatible with PHP `fnmatch` (e.g., '*.php')
- `max_depth: int` - Limit tree depth (default: unlimited)

*Display:*
- `show_size: bool` - Show file sizes in human-readable format (default: false)
- `show_date: bool` - Show modification dates (default: false)
- `show_permissions: bool` - Show Unix permissions (default: false)

*Sorting:*
- `sort_by: string` - Sort by: 'name', 'size', 'date', 'type' (default: null = preserve order)
- `sort_order: string` - Sort order: 'asc', 'desc' (default: 'asc')

*Visual:*
- `colors: bool` - Use ANSI colors for terminal output (default: false)

**Example:**
```php
$printer = new TreePrinter();

// Simple tree
echo $printer->print($tree);

// With icons and colors
echo $printer->print($tree, [
    'icons' => true,
    'colors' => true,
]);

// Show only PHP files with sizes, sorted by size
echo $printer->print($tree, [
    'pattern' => '*.php',
    'show_size' => true,
    'sort_by' => 'size',
    'sort_order' => 'desc',
]);

// Directories only with depth limit
echo $printer->print($tree, [
    'dirs_only' => true,
    'max_depth' => 2,
]);
```

### Traversal

#### `TreeTraverser`
**Final class** that traverses tree structures using the visitor pattern.

**Methods:**
- `addVisitor(VisitorInterface $visitor): self` - Add a visitor
- `traverse(TreeNode $node): void` - Traverse the tree

#### `CollectorVisitor`
**Final class** that collects files and directories during traversal.

**Methods:**
- `getFiles(): array` - Get collected file paths
- `getDirectories(): array` - Get collected directory paths
- `getTotalFiles(): int` - Get file count
- `getTotalDirectories(): int` - Get directory count
- `getSummary(): string` - Get formatted summary

#### `TreeFlattener`
**Final class** utility for flattening and rebuilding trees.

**Methods:**
- `static flatten(TreeNode $tree): array` - Flatten tree to paths array
- `static buildTree(array $paths, ?string $rootPath = null): Tree` - Build tree from paths

### Comparison

#### `TreeDiff`
**Final class** that compares two trees and identifies differences.

**Methods:**
- `static compare(TreeNode $oldTree, TreeNode $newTree): DiffResult` - Compare two trees

#### `DiffResult`
**Readonly class** containing the results of a tree comparison.

**Methods:**
- `getAdded(): array` - Get nodes added in new tree
- `getRemoved(): array` - Get nodes removed from old tree
- `getUnchanged(): array` - Get nodes present in both trees
- `hasChanges(): bool` - Check if there are any changes
- `getSummary(): string` - Get compact summary (e.g., "+5 -3")
- `getDetailedSummary(): string` - Get detailed summary with counts

#### `DiffPrinter`
**Final class** that prints visual representations of tree differences.

**Methods:**
- `print(DiffResult $diff, array $options = []): string` - Print diff as tree with +/- markers
- `printSummary(DiffResult $diff): string` - Print detailed summary
- `printUnified(DiffResult $diff, string $oldLabel, string $newLabel): string` - Print unified diff format

## Testing

Run the test suite:

```bash
composer test
```

Run static analysis:

```bash
composer phpstan
```

Run code style fixer:

```bash
composer cs-fix
```

## Contributing

Contributions are welcome! Please feel free to submit a Pull Request.

1. Fork the repository
2. Create your feature branch (`git checkout -b feature/amazing-feature`)
3. Commit your changes (`git commit -m 'Add some amazing feature'`)
4. Push to the branch (`git push origin feature/amazing-feature`)
5. Open a Pull Request

## License

This project is licensed under the MIT License - see the [LICENSE](LICENSE) file for details.

## Documentation

For comprehensive documentation and guides:

- **[Complete Usage Guide](_dev/docs/GUIDE.md)** - In-depth guide with detailed examples and best practices
- **[API Reference](_dev/docs/API.md)** - Complete API documentation for all classes and methods
- **[Examples](_dev/examples/)** - Working code examples demonstrating all features
- **[Changelog](CHANGELOG.md)** - Version history and changes

### Quick Links

- [Building Trees](_dev/docs/GUIDE.md#building-trees)
- [Parsing Trees](_dev/docs/GUIDE.md#parsing-trees)
- [Tree Traversal](_dev/docs/GUIDE.md#traversing-trees)
- [Tree Manipulation](_dev/docs/GUIDE.md#manipulating-trees)
- [Custom Visitors](_dev/docs/GUIDE.md#creating-custom-visitors)
- [Real-World Examples](_dev/examples/06-real-world-usage.php)

## Examples

The `examples/` directory contains comprehensive working examples:

- **[01-basic-usage.php](_dev/examples/01-basic-usage.php)** - Basic tree building and printing
- **[02-parsing-formats.php](_dev/examples/02-parsing-formats.php)** - Parsing different tree formats
- **[03-tree-traversal.php](_dev/examples/03-tree-traversal.php)** - Tree traversal with visitors
- **[04-tree-manipulation.php](_dev/examples/04-tree-manipulation.php)** - Split, merge, and append operations
- **[05-custom-visitor.php](_dev/examples/05-custom-visitor.php)** - Custom visitor implementations
- **[06-real-world-usage.php](_dev/examples/06-real-world-usage.php)** - Real-world usage scenarios
- **[07-tree-diff.php](_dev/examples/07-tree-diff.php)** - Tree comparison and diff operations
- **[08-provider-pattern.php](_dev/examples/08-provider-pattern.php)** - Using different data source providers
- **[09-enhanced-printer.php](_dev/examples/09-enhanced-printer.php)** - Enhanced printer options (filtering, sorting, formatting)

Run any example:
```bash
php examples/01-basic-usage.php
```

## Support

If you find this package useful, please consider starring the repository!
