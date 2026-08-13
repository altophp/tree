<?php

declare(strict_types=1);

/*
 * This file is part of the ALTO library.
 *
 * © 2026-present Simon André
 *
 * For full copyright and license information, please see
 * the LICENSE file distributed with this source code.
 */

namespace Alto\Tree\Tests\Printer;

use Alto\Tree\Printer\TreePrinter;
use Alto\Tree\Tree;
use Alto\Tree\TreeNode;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(TreePrinter::class)]
class TreePrinterTest extends TestCase
{
    public function testPrintEmptyTree(): void
    {
        $printer = new TreePrinter();
        $tree = new Tree('/root');

        $output = $printer->print($tree);

        $this->assertEquals('', $output);
    }

    public function testPrintSingleFile(): void
    {
        $printer = new TreePrinter();
        $tree = new Tree('/root');
        $file = new TreeNode('/root/file.txt', false);
        $tree->addChild($file);

        $output = $printer->print($tree);

        $expected = "└── file.txt\n";
        $this->assertEquals($expected, $output);
    }

    public function testPrintMultipleFiles(): void
    {
        $printer = new TreePrinter();
        $tree = new Tree('/root');
        $file1 = new TreeNode('/root/file1.txt', false);
        $file2 = new TreeNode('/root/file2.txt', false);
        $tree->addChild($file1);
        $tree->addChild($file2);

        $output = $printer->print($tree);

        $expected = "├── file1.txt\n└── file2.txt\n";
        $this->assertEquals($expected, $output);
    }

    public function testPrintSingleDirectory(): void
    {
        $printer = new TreePrinter();
        $tree = new Tree('/root');
        $dir = new TreeNode('/root/subdir', true);
        $tree->addChild($dir);

        $output = $printer->print($tree);

        $expected = "└── subdir\n";
        $this->assertEquals($expected, $output);
    }

    public function testPrintDirectoryWithChildren(): void
    {
        $printer = new TreePrinter();
        $tree = new Tree('/root');
        $dir = new TreeNode('/root/subdir', true);
        $file = new TreeNode('/root/subdir/file.txt', false);
        $dir->addChild($file);
        $tree->addChild($dir);

        $output = $printer->print($tree);

        $expected = "└── subdir\n    └── file.txt\n";
        $this->assertEquals($expected, $output);
    }

    public function testPrintNestedStructure(): void
    {
        $printer = new TreePrinter();
        $tree = new Tree('/root');

        $dir1 = new TreeNode('/root/dir1', true);
        $file1 = new TreeNode('/root/dir1/file1.txt', false);
        $dir1->addChild($file1);

        $dir2 = new TreeNode('/root/dir2', true);
        $file2 = new TreeNode('/root/dir2/file2.txt', false);
        $dir2->addChild($file2);

        $tree->addChild($dir1);
        $tree->addChild($dir2);

        $output = $printer->print($tree);

        $expected = "├── dir1\n│   └── file1.txt\n└── dir2\n    └── file2.txt\n";
        $this->assertEquals($expected, $output);
    }

    public function testPrintComplexStructure(): void
    {
        $printer = new TreePrinter();
        $tree = new Tree('/project');

        $src = new TreeNode('/project/src', true);
        $controller = new TreeNode('/project/src/Controller', true);
        $userController = new TreeNode('/project/src/Controller/UserController.php', false);
        $model = new TreeNode('/project/src/Model', true);
        $user = new TreeNode('/project/src/Model/User.php', false);

        $controller->addChild($userController);
        $model->addChild($user);
        $src->addChild($controller);
        $src->addChild($model);

        $tests = new TreeNode('/project/tests', true);
        $userTest = new TreeNode('/project/tests/UserTest.php', false);
        $tests->addChild($userTest);

        $tree->addChild($src);
        $tree->addChild($tests);

        $output = $printer->print($tree);

        $expected = <<<EOT
├── src
│   ├── Controller
│   │   └── UserController.php
│   └── Model
│       └── User.php
└── tests
    └── UserTest.php

EOT;

        $this->assertEquals($expected, $output);
    }

    public function testPrintWithMultipleLevels(): void
    {
        $printer = new TreePrinter();
        $tree = new Tree('/root');

        $a = new TreeNode('/root/a', true);
        $b = new TreeNode('/root/a/b', true);
        $c = new TreeNode('/root/a/b/c', true);
        $file = new TreeNode('/root/a/b/c/deep.txt', false);

        $c->addChild($file);
        $b->addChild($c);
        $a->addChild($b);
        $tree->addChild($a);

        $output = $printer->print($tree);

        $expected = "└── a\n    └── b\n        └── c\n            └── deep.txt\n";
        $this->assertEquals($expected, $output);
    }

    public function testPrintWithMixedContent(): void
    {
        $printer = new TreePrinter();
        $tree = new Tree('/root');

        $file1 = new TreeNode('/root/file1.txt', false);
        $dir = new TreeNode('/root/directory', true);
        $file2 = new TreeNode('/root/directory/file2.txt', false);
        $file3 = new TreeNode('/root/file3.txt', false);

        $dir->addChild($file2);
        $tree->addChild($file1);
        $tree->addChild($dir);
        $tree->addChild($file3);

        $output = $printer->print($tree);

        $expected = "├── file1.txt\n├── directory\n│   └── file2.txt\n└── file3.txt\n";
        $this->assertEquals($expected, $output);
    }

    public function testPrintWithThreeChildren(): void
    {
        $printer = new TreePrinter();
        $tree = new Tree('/root');

        $child1 = new TreeNode('/root/child1', true);
        $child2 = new TreeNode('/root/child2', true);
        $child3 = new TreeNode('/root/child3', true);

        $tree->addChild($child1);
        $tree->addChild($child2);
        $tree->addChild($child3);

        $output = $printer->print($tree);

        $expected = "├── child1\n├── child2\n└── child3\n";
        $this->assertEquals($expected, $output);
    }

    public function testPrintAcceptsOptionsParameter(): void
    {
        $printer = new TreePrinter();
        $tree = new Tree('/root');
        $file = new TreeNode('/root/file.txt', false);
        $tree->addChild($file);

        // Options parameter should be accepted but not affect output in current implementation
        $output = $printer->print($tree, ['some' => 'option']);

        $expected = "└── file.txt\n";
        $this->assertEquals($expected, $output);
    }

    public function testPrintMultipleSiblings(): void
    {
        $printer = new TreePrinter();
        $tree = new Tree('/root');

        $dir1 = new TreeNode('/root/dir1', true);
        $file1 = new TreeNode('/root/dir1/file1.txt', false);
        $file2 = new TreeNode('/root/dir1/file2.txt', false);

        $dir1->addChild($file1);
        $dir1->addChild($file2);
        $tree->addChild($dir1);

        $output = $printer->print($tree);

        $expected = "└── dir1\n    ├── file1.txt\n    └── file2.txt\n";
        $this->assertEquals($expected, $output);
    }
}
