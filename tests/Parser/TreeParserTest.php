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

namespace Alto\Tree\Tests\Parser;

use Alto\Tree\Parser\TreeParser;
use Alto\Tree\Tree;
use Alto\Tree\TreeNode;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(TreeParser::class)]
class TreeParserTest extends TestCase
{
    private TreeParser $parser;

    protected function setUp(): void
    {
        $this->parser = new TreeParser();
    }

    public function testParseEmptyTree(): void
    {
        $input = 'src';
        $tree = $this->parser->parse($input);

        $this->assertInstanceOf(Tree::class, $tree);
        $this->assertEquals('src', $tree->path);
        $this->assertCount(0, $tree->children);
    }

    public function testParseSingleLevelTree(): void
    {
        $input = "src\n├── file1.php\n└── file2.php";
        $tree = $this->parser->parse($input);

        $this->assertInstanceOf(Tree::class, $tree);
        $this->assertEquals('src', $tree->path);
        $this->assertCount(2, $tree->children);
        $this->assertArrayHasKey('file1.php', $tree->children);
        $this->assertArrayHasKey('file2.php', $tree->children);

        $file1 = $tree->children['file1.php'];
        $this->assertEquals('src/file1.php', $file1->path);
        $this->assertEquals('file1.php', $file1->name);
        $this->assertFalse($file1->isDir);

        $file2 = $tree->children['file2.php'];
        $this->assertEquals('src/file2.php', $file2->path);
        $this->assertEquals('file2.php', $file2->name);
        $this->assertFalse($file2->isDir);
    }

    public function testParseMultiLevelTree(): void
    {
        $input = "project\n├── src\n│   ├── Model\n│   │   └── User.php\n│   └── Controller\n│       └── UserController.php\n└── tests";
        $tree = $this->parser->parse($input);

        $this->assertInstanceOf(Tree::class, $tree);
        $this->assertEquals('project', $tree->path);
        $this->assertCount(2, $tree->children);

        // Check src directory
        $this->assertArrayHasKey('src', $tree->children);
        $src = $tree->children['src'];
        $this->assertEquals('project/src', $src->path);
        $this->assertTrue($src->isDir);
        $this->assertCount(2, $src->children);

        // Check Model directory
        $this->assertArrayHasKey('Model', $src->children);
        $model = $src->children['Model'];
        $this->assertEquals('project/src/Model', $model->path);
        $this->assertTrue($model->isDir);
        $this->assertCount(1, $model->children);

        // Check User.php file
        $this->assertArrayHasKey('User.php', $model->children);
        $user = $model->children['User.php'];
        $this->assertEquals('project/src/Model/User.php', $user->path);
        $this->assertFalse($user->isDir);

        // Check Controller directory
        $this->assertArrayHasKey('Controller', $src->children);
        $controller = $src->children['Controller'];
        $this->assertEquals('project/src/Controller', $controller->path);
        $this->assertTrue($controller->isDir);
        $this->assertCount(1, $controller->children);

        // Check UserController.php file
        $this->assertArrayHasKey('UserController.php', $controller->children);
        $userController = $controller->children['UserController.php'];
        $this->assertEquals('project/src/Controller/UserController.php', $userController->path);
        $this->assertFalse($userController->isDir);

        // Check tests directory
        $this->assertArrayHasKey('tests', $tree->children);
        $tests = $tree->children['tests'];
        $this->assertEquals('project/tests', $tests->path);
        $this->assertTrue($tests->isDir);
        $this->assertCount(0, $tests->children);
    }

    public function testParseWithEmptyLines(): void
    {
        $input = "src\n\n├── file1.php\n\n└── file2.php\n\n";
        $tree = $this->parser->parse($input);

        $this->assertInstanceOf(Tree::class, $tree);
        $this->assertEquals('src', $tree->path);
        $this->assertCount(2, $tree->children);
        $this->assertArrayHasKey('file1.php', $tree->children);
        $this->assertArrayHasKey('file2.php', $tree->children);
    }

    public function testParseMixedDirectoriesAndFiles(): void
    {
        $input = "src\n├── lib\n│   ├── helper.php\n│   └── util.php\n├── main.php\n└── config";
        $tree = $this->parser->parse($input);

        $this->assertInstanceOf(Tree::class, $tree);
        $this->assertEquals('src', $tree->path);
        $this->assertCount(3, $tree->children);

        // Check lib directory
        $this->assertArrayHasKey('lib', $tree->children);
        $lib = $tree->children['lib'];
        $this->assertEquals('src/lib', $lib->path);
        $this->assertTrue($lib->isDir);
        $this->assertCount(2, $lib->children);

        // Check helper.php and util.php
        $this->assertArrayHasKey('helper.php', $lib->children);
        $this->assertArrayHasKey('util.php', $lib->children);

        // Check main.php file
        $this->assertArrayHasKey('main.php', $tree->children);
        $main = $tree->children['main.php'];
        $this->assertEquals('src/main.php', $main->path);
        $this->assertFalse($main->isDir);

        // Check config directory
        $this->assertArrayHasKey('config', $tree->children);
        $config = $tree->children['config'];
        $this->assertEquals('src/config', $config->path);
        $this->assertTrue($config->isDir);
    }

    public function testItParseDashedLists(): void
    {
        $input = <<<EOT
src
- Twig
  - Extension
  - FooBar.php
- UseCase
  - Analytical
  - Sample.php
EOT;

        $tree = $this->parser->parse($input);

        $this->assertInstanceOf(TreeNode::class, $tree);

        $this->assertEquals('src', $tree->path);
        $this->assertCount(2, $tree->children);

        $this->assertArrayHasKey('Twig', $tree->children);
        $this->assertArrayHasKey('Extension', $tree->children['Twig']->children);
        $this->assertArrayHasKey('FooBar.php', $tree->children['Twig']->children);

        $this->assertArrayHasKey('UseCase', $tree->children);
        $this->assertArrayHasKey('Analytical', $tree->children['UseCase']->children);
        $this->assertArrayHasKey('Sample.php', $tree->children['UseCase']->children);
    }

    public function testItParseBulletLists(): void
    {
        $input = <<<EOT
src
* Twig
  * Extension
  * FooBar.php
* UseCase
  * Analytical
  * Sample.php
EOT;

        $tree = $this->parser->parse($input);

        $this->assertInstanceOf(TreeNode::class, $tree);

        $this->assertEquals('src', $tree->path);
        $this->assertCount(2, $tree->children);

        $this->assertArrayHasKey('Twig', $tree->children);
        $this->assertArrayHasKey('Extension', $tree->children['Twig']->children);
        $this->assertArrayHasKey('FooBar.php', $tree->children['Twig']->children);

        $this->assertArrayHasKey('UseCase', $tree->children);
        $this->assertArrayHasKey('Analytical', $tree->children['UseCase']->children);
        $this->assertArrayHasKey('Sample.php', $tree->children['UseCase']->children);
    }

    public function testItParseIndentedLists(): void
    {
        $input = <<<EOT
src
  Twig
    Extension
    FooBar.php
  UseCase
    Analytical
    Sample.php
EOT;

        $tree = $this->parser->parse($input);

        $this->assertInstanceOf(TreeNode::class, $tree);

        $this->assertEquals('src', $tree->path);
        $this->assertCount(2, $tree->children);

        $this->assertArrayHasKey('Twig', $tree->children);
        $this->assertArrayHasKey('Extension', $tree->children['Twig']->children);
        $this->assertArrayHasKey('FooBar.php', $tree->children['Twig']->children);

        $this->assertArrayHasKey('UseCase', $tree->children);
        $this->assertArrayHasKey('Analytical', $tree->children['UseCase']->children);
        $this->assertArrayHasKey('Sample.php', $tree->children['UseCase']->children);
    }

    public function testItParseIndentedListsWith4Spaces(): void
    {
        $input = <<<EOT
src
    Twig
        Extension
        FooBar.php
    UseCase
        Analytical
        Sample.php
EOT;

        $tree = $this->parser->parse($input);

        $this->assertInstanceOf(TreeNode::class, $tree);

        $this->assertEquals('src', $tree->path);
        $this->assertCount(2, $tree->children);

        $this->assertArrayHasKey('Twig', $tree->children);
        $this->assertArrayHasKey('Extension', $tree->children['Twig']->children);
        $this->assertArrayHasKey('FooBar.php', $tree->children['Twig']->children);

        $this->assertArrayHasKey('UseCase', $tree->children);
        $this->assertArrayHasKey('Analytical', $tree->children['UseCase']->children);
        $this->assertArrayHasKey('Sample.php', $tree->children['UseCase']->children);
    }
}
