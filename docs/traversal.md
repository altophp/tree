# Traversing trees

Use the built-in collector when you need file and directory paths or counts.

```php
use Alto\Tree\Traverser\TreeTraverser;
use Alto\Tree\Visitor\CollectorVisitor;

$collector = new CollectorVisitor();
$traverser = new TreeTraverser();
$traverser->addVisitor($collector);
$traverser->traverse($tree);

$files = $collector->getFiles();
$directories = $collector->getDirectories();
echo $collector->getSummary();
```

Traversal is depth-first and follows child insertion order. The collector
counts a directory when the traverser enters it; an empty directory is visited
as a node but is not entered.

## Add custom behavior

```php
use Alto\Tree\TreeNode;
use Alto\Tree\Visitor\VisitorInterface;

$visitor = new class implements VisitorInterface {
    public array $phpFiles = [];

    public function visitNode(TreeNode $node, int $depth): void
    {
        if (!$node->isDir && str_ends_with($node->name, '.php')) {
            $this->phpFiles[] = $node->path;
        }
    }

    public function enterDirectory(TreeNode $node, int $depth): void {}

    public function leaveDirectory(TreeNode $node, int $depth): void {}
};

$traverser->addVisitor($visitor);
$traverser->traverse($tree);
```

Several visitors may be registered on one traverser. Each receives
`visitNode()`, then directory enter and leave callbacks where applicable.
