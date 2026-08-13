# Building trees

`TreeBuilder` normalizes several sources into the same `Tree` and `TreeNode`
model.

## Known paths

```php
use Alto\Tree\TreeBuilder;

$tree = TreeBuilder::fromPaths([
    'project/src/App.php',
    'project/config/services.php',
], 'project');
```

Path segments containing a dot are treated as files; other final segments are
treated as directories. Use a custom provider when that filename heuristic is
not sufficient.

## A directory

```php
$tree = TreeBuilder::fromFilesystem(__DIR__.'/project', [
    'max_depth' => 3,
    'exclude' => ['vendor', '.git', '*.log'],
    'include_hidden' => false,
    'with_metadata' => true,
    'follow_symlinks' => false,
]);
```

Metadata contains file size, modification time, permissions, readability, and
writability. Exclusion matches both substrings and `fnmatch()` patterns.

## A Git repository

```php
$tracked = TreeBuilder::fromGit(__DIR__.'/project');
$staged = TreeBuilder::fromGit(__DIR__.'/project', ['staged_only' => true]);
$modified = TreeBuilder::fromGit(__DIR__.'/project', ['modified_only' => true]);
$betweenRefs = TreeBuilder::fromGit(__DIR__.'/project', ['diff' => 'main..feature']);
$atCommit = TreeBuilder::fromGit(__DIR__.'/project', ['commit' => 'v1.0.0']);
```

The provider executes the local `git` command. Use it only with trusted local
repository paths and references. A non-directory or non-repository path raises
`InvalidArgumentException`; a failed Git command raises `RuntimeException`.

## A custom provider

```php
use Alto\Tree\Provider\NodeData;
use Alto\Tree\Provider\TreeSourceProviderInterface;
use Alto\Tree\TreeBuilder;

$provider = new class implements TreeSourceProviderInterface {
    public function getRootPath(): string
    {
        return 'packages';
    }

    public function getNodes(): array
    {
        return [
            NodeData::directory('packages/alto'),
            NodeData::file('packages/alto/composer.json'),
        ];
    }
};

$tree = TreeBuilder::from($provider);
```

Providers must include any intermediate directory nodes needed to connect
their paths to the root.
