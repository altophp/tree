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

namespace Alto\Tree\Tests;

use Alto\Tree\Tree;
use Alto\Tree\TreeBuilder;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

#[CoversClass(TreeBuilder::class)]
class TreeBuilderTest extends TestCase
{
    public function testFromPathsWithEmptyArray(): void
    {
        $tree = TreeBuilder::fromPaths([], 'root');

        $this->assertInstanceOf(Tree::class, $tree);
        $this->assertEquals('root', $tree->path);
        $this->assertCount(0, $tree->children);
    }

    public function testFromPathsWithSingleFile(): void
    {
        $paths = ['root/file.txt'];
        $tree = TreeBuilder::fromPaths($paths, 'root');

        $this->assertEquals('root', $tree->path);
        $this->assertCount(1, $tree->children);
        $this->assertArrayHasKey('file.txt', $tree->children);
        $this->assertEquals('root/file.txt', $tree->children['file.txt']->path);
        $this->assertFalse($tree->children['file.txt']->isDir);
    }

    public function testFromPathsWithSingleDirectory(): void
    {
        $paths = ['root/directory'];
        $tree = TreeBuilder::fromPaths($paths, 'root');

        $this->assertEquals('root', $tree->path);
        $this->assertCount(1, $tree->children);
        $this->assertArrayHasKey('directory', $tree->children);
        $this->assertTrue($tree->children['directory']->isDir);
    }

    public function testFromPathsWithNestedStructure(): void
    {
        $paths = [
            'project/src/Controller/UserController.php',
            'project/src/Model/User.php',
            'project/tests/UserTest.php',
        ];
        $tree = TreeBuilder::fromPaths($paths, 'project');

        $this->assertEquals('project', $tree->path);
        $this->assertCount(2, $tree->children);

        // Check src directory
        $this->assertArrayHasKey('src', $tree->children);
        $src = $tree->children['src'];
        $this->assertTrue($src->isDir);
        $this->assertCount(2, $src->children);

        // Check Controller directory
        $this->assertArrayHasKey('Controller', $src->children);
        $controller = $src->children['Controller'];
        $this->assertTrue($controller->isDir);
        $this->assertCount(1, $controller->children);
        $this->assertArrayHasKey('UserController.php', $controller->children);
        $this->assertFalse($controller->children['UserController.php']->isDir);

        // Check Model directory
        $this->assertArrayHasKey('Model', $src->children);
        $model = $src->children['Model'];
        $this->assertTrue($model->isDir);
        $this->assertCount(1, $model->children);
        $this->assertArrayHasKey('User.php', $model->children);
        $this->assertFalse($model->children['User.php']->isDir);

        // Check tests directory
        $this->assertArrayHasKey('tests', $tree->children);
        $tests = $tree->children['tests'];
        $this->assertTrue($tests->isDir);
        $this->assertCount(1, $tests->children);
        $this->assertArrayHasKey('UserTest.php', $tests->children);
    }

    public function testFromPathsDefaultRootPath(): void
    {
        $paths = ['src/file.txt'];
        $tree = TreeBuilder::fromPaths($paths);

        $this->assertEquals('src', $tree->path);
    }

    /**
     * @param array<string>       $paths
     * @param array<string, bool> $expectedStructure
     */
    #[DataProvider('pathsProvider')]
    public function testFromPathsWithVariousPaths(array $paths, string $rootPath, array $expectedStructure): void
    {
        /** @var array<string> $paths */
        /** @var array<string, bool> $expectedStructure */
        $tree = TreeBuilder::fromPaths($paths, $rootPath);

        $this->assertEquals($rootPath, $tree->path);
        $this->assertCount(count($expectedStructure), $tree->children);

        foreach ($expectedStructure as $childName => $isDir) {
            $this->assertArrayHasKey($childName, $tree->children);
            $this->assertEquals($isDir, $tree->children[$childName]->isDir);
        }
    }

    /**
     * @return array<string, array{array<string>, string, array<string, bool>}>
     */
    public static function pathsProvider(): array
    {
        return [
            'files only' => [
                ['root/file1.txt', 'root/file2.php', 'root/file3.md'],
                'root',
                ['file1.txt' => false, 'file2.php' => false, 'file3.md' => false],
            ],
            'directories only' => [
                ['root/dir1', 'root/dir2', 'root/dir3'],
                'root',
                ['dir1' => true, 'dir2' => true, 'dir3' => true],
            ],
            'mixed content' => [
                ['root/file.txt', 'root/directory', 'root/another.php'],
                'root',
                ['file.txt' => false, 'directory' => true, 'another.php' => false],
            ],
        ];
    }

    public function testFromPathsDetectsFilesWithDots(): void
    {
        $paths = [
            'root/file.txt',
            'root/config.yml',
            'root/script.sh',
            'root/directory',
        ];
        $tree = TreeBuilder::fromPaths($paths, 'root');

        $this->assertFalse($tree->children['file.txt']->isDir);
        $this->assertFalse($tree->children['config.yml']->isDir);
        $this->assertFalse($tree->children['script.sh']->isDir);
        $this->assertTrue($tree->children['directory']->isDir);
    }

    public function testFromPathsWithDeepNesting(): void
    {
        $paths = ['root/a/b/c/d/e/file.txt'];
        $tree = TreeBuilder::fromPaths($paths, 'root');

        $this->assertArrayHasKey('a', $tree->children);
        $a = $tree->children['a'];
        $this->assertTrue($a->isDir);

        $this->assertArrayHasKey('b', $a->children);
        $b = $a->children['b'];
        $this->assertTrue($b->isDir);

        $this->assertArrayHasKey('c', $b->children);
        $c = $b->children['c'];
        $this->assertTrue($c->isDir);

        $this->assertArrayHasKey('d', $c->children);
        $d = $c->children['d'];
        $this->assertTrue($d->isDir);

        $this->assertArrayHasKey('e', $d->children);
        $e = $d->children['e'];
        $this->assertTrue($e->isDir);

        $this->assertArrayHasKey('file.txt', $e->children);
        $this->assertFalse($e->children['file.txt']->isDir);
    }

    public function testFromPathsWithLeadingAndTrailingSlashes(): void
    {
        $paths = [
            '/root/dir1/file.txt',
            'root/dir2/file.txt/',
            '/root/dir3/',
        ];
        $tree = TreeBuilder::fromPaths($paths, 'root');

        $this->assertArrayHasKey('dir1', $tree->children);
        $this->assertArrayHasKey('dir2', $tree->children);
        $this->assertArrayHasKey('dir3', $tree->children);
    }

    public function testFromPathsBuildsCorrectPaths(): void
    {
        $paths = ['root/src/Controller/UserController.php'];
        $tree = TreeBuilder::fromPaths($paths, 'root');

        $this->assertEquals('root/src', $tree->children['src']->path);
        $this->assertEquals('root/src/Controller', $tree->children['src']->children['Controller']->path);
        $this->assertEquals('root/src/Controller/UserController.php', $tree->children['src']->children['Controller']->children['UserController.php']->path);
    }
}
