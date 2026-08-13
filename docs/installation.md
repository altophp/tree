# Installation

Alto Tree requires PHP 8.2 or later and the Mbstring extension.

```bash
composer require alto/tree
```

## Verify the installation

```php
<?php

require __DIR__.'/vendor/autoload.php';

use Alto\Tree\TreeBuilder;

$tree = TreeBuilder::fromPaths(['project/src/App.php'], 'project');

echo $tree->children['src']->children['App.php']->path;
```

The script prints `project/src/App.php`.
