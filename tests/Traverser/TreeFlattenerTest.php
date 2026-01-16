<?php

declare(strict_types=1);

/*
 * This file is part of the ALTO library.
 *
 * © 2026–present Simon André
 *
 * For full copyright and license information, please see
 * the LICENSE file distributed with this source code.
 */

namespace Alto\Tree\Tests\Traverser;

use Alto\Tree\Traverser\TreeFlattener;
use Alto\Tree\Tree;
use Alto\Tree\TreeNode;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

#[CoversClass(TreeFlattener::class)]
class TreeFlattenerTest extends TestCase
{
    public function testFlattenEmptyTree(): void
    {
        $tree = new Tree('/root');

        $paths = TreeFlattener::flatten($tree);

        $this->assertCount(1, $paths);
        $this->assertEquals(['/root'], $paths);
    }

    public function testFlattenSingleFile(): void
    {
        $tree = new Tree('/root');
        $file = new TreeNode('/root/file.txt', false);
        $tree->addChild($file);

        $paths = TreeFlattener::flatten($tree);

        $this->assertCount(2, $paths);
        $this->assertContains('/root', $paths);
        $this->assertContains('/root/file.txt', $paths);
    }

    public function testFlattenNestedStructure(): void
    {
        $tree = new Tree('/root');
        $dir = new TreeNode('/root/dir', true);
        $file1 = new TreeNode('/root/file1.txt', false);
        $file2 = new TreeNode('/root/dir/file2.txt', false);

        $dir->addChild($file2);
        $tree->addChild($dir);
        $tree->addChild($file1);

        $paths = TreeFlattener::flatten($tree);

        $this->assertCount(4, $paths);
        $this->assertContains('/root', $paths);
        $this->assertContains('/root/dir', $paths);
        $this->assertContains('/root/file1.txt', $paths);
        $this->assertContains('/root/dir/file2.txt', $paths);
    }

    public function testBuildTreeFromEmptyArray(): void
    {
        $tree = TreeFlattener::buildTree([], 'custom-root');

        $this->assertInstanceOf(Tree::class, $tree);
        $this->assertEquals('custom-root', $tree->path);
        $this->assertCount(0, $tree->children);
    }

    public function testBuildTreeFromSinglePath(): void
    {
        $paths = ['root/file.txt'];
        $tree = TreeFlattener::buildTree($paths, 'root');

        $this->assertEquals('root', $tree->path);
        $this->assertCount(1, $tree->children);
        $this->assertArrayHasKey('file.txt', $tree->children);
    }

    public function testBuildTreeFromMultiplePaths(): void
    {
        $paths = [
            'project/src/Controller/UserController.php',
            'project/src/Model/User.php',
            'project/tests/UserTest.php',
        ];
        $tree = TreeFlattener::buildTree($paths, 'project');

        $this->assertEquals('project', $tree->path);
        $this->assertArrayHasKey('src', $tree->children);
        $this->assertArrayHasKey('tests', $tree->children);
    }

    public function testBuildTreeWithNullRootPath(): void
    {
        $paths = [
            'common/dir/file1.txt',
            'common/dir/file2.txt',
        ];
        $tree = TreeFlattener::buildTree($paths);

        $this->assertInstanceOf(Tree::class, $tree);
        $this->assertEquals('common/dir', $tree->path);
    }

    public function testBuildTreeFindsCommonBasePath(): void
    {
        $paths = [
            'base/path/file1.txt',
            'base/path/file2.txt',
            'base/path/subdir/file3.txt',
        ];
        $tree = TreeFlattener::buildTree($paths);

        $this->assertEquals('base/path', $tree->path);
    }

    /**
     * @param array<string> $paths
     */
    #[DataProvider('commonBasePathProvider')]
    public function testBuildTreeWithVariousCommonBasePaths(array $paths, string $expectedBase): void
    {
        /** @var array<string> $paths */
        $tree = TreeFlattener::buildTree($paths);

        $this->assertEquals($expectedBase, $tree->path);
    }

    /**
     * @return array<string, array{array<string>, string}>
     */
    public static function commonBasePathProvider(): array
    {
        return [
            'same directory' => [
                ['dir/file1.txt', 'dir/file2.txt'],
                'dir',
            ],
            'nested structure' => [
                ['a/b/c/file1.txt', 'a/b/c/file2.txt', 'a/b/c/d/file3.txt'],
                'a/b/c',
            ],
            'different directories' => [
                ['dir1/file1.txt', 'dir2/file2.txt'],
                '',
            ],
            'single path' => [
                ['path/to/file.txt'],
                'path/to',
            ],
        ];
    }

    public function testFlattenAndRebuild(): void
    {
        // Arrange: Create original tree
        $original = new Tree('/root');
        $dir = new TreeNode('/root/dir', true);
        $file1 = new TreeNode('/root/file1.txt', false);
        $file2 = new TreeNode('/root/dir/file2.txt', false);

        $dir->addChild($file2);
        $original->addChild($dir);
        $original->addChild($file1);

        // Act: Flatten and rebuild
        $paths = TreeFlattener::flatten($original);
        array_shift($paths); // Remove root path
        $rebuilt = TreeFlattener::buildTree($paths, 'root');

        // Assert: Rebuilt tree has same structure
        $this->assertEquals('root', $rebuilt->path);
        $this->assertArrayHasKey('dir', $rebuilt->children);
        $this->assertArrayHasKey('file1.txt', $rebuilt->children);
        $this->assertArrayHasKey('file2.txt', $rebuilt->children['dir']->children);
    }

    public function testBuildTreeHandlesRootDirectories(): void
    {
        $paths = [
            'file1.txt',
            'file2.txt',
        ];
        $tree = TreeFlattener::buildTree($paths);

        // When files have no common directory, should return empty string
        $this->assertEquals('', $tree->path);
    }

    public function testFlattenPreservesOrder(): void
    {
        $tree = new Tree('/root');
        $child1 = new TreeNode('/root/child1', true);
        $child2 = new TreeNode('/root/child1/grandchild', false);
        $child3 = new TreeNode('/root/child3', false);

        $child1->addChild($child2);
        $tree->addChild($child1);
        $tree->addChild($child3);

        $paths = TreeFlattener::flatten($tree);

        // Should be in depth-first traversal order
        $this->assertEquals('/root', $paths[0]);
        $this->assertEquals('/root/child1', $paths[1]);
        $this->assertEquals('/root/child1/grandchild', $paths[2]);
        $this->assertEquals('/root/child3', $paths[3]);
    }
}
