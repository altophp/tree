# Printing trees

`TreePrinter::print()` returns box-drawing output for a `TreeNode`. The root
name is omitted, leaving only its descendants.

```php
use Alto\Tree\Printer\TreePrinter;

$output = (new TreePrinter())->print($tree, [
    'show_hidden' => false,
    'pattern' => '*.php',
    'max_depth' => 3,
    'sort_by' => 'name',
    'sort_order' => 'asc',
]);
```

Available filters are `show_hidden`, `files_only`, `dirs_only`, `pattern`, and
`max_depth`. Sort by `name`, `size`, `date`, or `type`, in `asc` or `desc`
order.

## Print metadata

Metadata output is meaningful when the tree was built from the filesystem
with `with_metadata` enabled:

```php
$output = (new TreePrinter())->print($tree, [
    'show_size' => true,
    'show_date' => true,
    'show_permissions' => true,
]);
```

Set `colors` to `true` to color directory names blue for terminal output.

`PrinterOptions::fromArray()` converts the option array into an immutable
object. The public printer accepts the array directly.
