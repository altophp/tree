# Comparing trees

`TreeDiff` compares paths in two trees and classifies them as added, removed,
or unchanged.

```php
use Alto\Tree\Diff\TreeDiff;
use Alto\Tree\TreeBuilder;

$before = TreeBuilder::fromPaths([
    'project/src/App.php',
    'project/README.md',
], 'project');

$after = TreeBuilder::fromPaths([
    'project/src/App.php',
    'project/tests/AppTest.php',
], 'project');

$diff = TreeDiff::compare($before, $after);

echo $diff->getSummary(); // +2 -1
```

Directories introduced by new paths count as additions. The root itself is
not included in the comparison.

Inspect `getAdded()`, `getRemoved()`, and `getUnchanged()` for maps keyed by
path. Count methods and `hasChanges()` provide quick decisions.

## Render the result

```php
use Alto\Tree\Diff\DiffPrinter;

$printer = new DiffPrinter();

echo $printer->print($diff);
echo $printer->printSummary($diff);
echo $printer->printUnified($diff, 'before', 'after');
```

`print()` accepts `show_unchanged => true` when the complete path comparison
is needed. Unified output is a path-level report, not a textual patch that can
be applied to a filesystem.
