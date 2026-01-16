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

namespace Alto\Tree\Tests;

use Alto\Tree\TreeNode;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(TreeNode::class)]
class TreeNodeTest extends TestCase
{
    public function testToStringForDirectory(): void
    {
        $path = '/path/to/dir';
        $node = new TreeNode($path, true);
        $expectedString = $path."\n".str_repeat('=', strlen($path))."\n";

        $this->assertSame($expectedString, (string) $node);
    }

    public function testToStringForFile(): void
    {
        $path = '/path/to/file.ext';
        $node = new TreeNode($path, false);
        $expectedString = $path."\n";

        $this->assertSame($expectedString, (string) $node);
    }

    public function testToStringWithChildrenDoesNotChangeOutput(): void
    {
        $parentDir = new TreeNode('/path/to/parent', true);
        $childFile = new TreeNode('/path/to/parent/child.txt', false);
        $childDir = new TreeNode('/path/to/parent/subdir', true);

        $parentDir->addChild($childFile);
        $parentDir->addChild($childDir);

        $expectedDirString = $parentDir->path."\n".str_repeat('=', strlen($parentDir->path))."\n";
        $this->assertSame($expectedDirString, (string) $parentDir);

        $parentFile = new TreeNode('/path/to/parent.txt', false);
        $parentFile->addChild($childFile);

        $expectedFileString = $parentFile->path."\n";
        $this->assertSame($expectedFileString, (string) $parentFile);
    }

    public function testAddChild(): void
    {
        $parent = new TreeNode('/parent', true);
        $child = new TreeNode('/parent/child', false);

        $parent->addChild($child);

        $this->assertCount(1, $parent->children);
        $this->assertArrayHasKey('child', $parent->children);
        $this->assertSame($child, $parent->children['child']);
    }

    public function testSplitWithEmptyPath(): void
    {
        $node = new TreeNode('/root', true);
        $child = new TreeNode('/root/child', false);
        $node->addChild($child);

        $result = $node->split('');

        $this->assertNotNull($result);
        $this->assertNotSame($node, $result);
        $this->assertEquals('/root', $result->path);
        $this->assertCount(1, $result->children);
    }

    public function testSplitWithMatchingPath(): void
    {
        $node = new TreeNode('/root', true);
        $child = new TreeNode('/root/child', false);
        $node->addChild($child);

        $result = $node->split('/root');

        $this->assertNotNull($result);
        $this->assertNotSame($node, $result);
        $this->assertEquals('/root', $result->path);
        $this->assertCount(1, $result->children);
    }

    public function testSplitWithAbsolutePath(): void
    {
        $root = new TreeNode('/root', true);
        $subdir = new TreeNode('/root/subdir', true);
        $file = new TreeNode('/root/subdir/file.txt', false);

        $subdir->addChild($file);
        $root->addChild($subdir);

        $result = $root->split('/root/subdir');

        $this->assertNotNull($result);
        $this->assertEquals('/root/subdir', $result->path);
        $this->assertCount(1, $result->children);
        $this->assertArrayHasKey('file.txt', $result->children);
    }

    public function testSplitWithRelativePath(): void
    {
        $root = new TreeNode('/root', true);
        $subdir = new TreeNode('/root/subdir', true);
        $file = new TreeNode('/root/subdir/file.txt', false);

        $subdir->addChild($file);
        $root->addChild($subdir);

        $result = $root->split('subdir');

        $this->assertNotNull($result);
        $this->assertEquals('/root/subdir', $result->path);
        $this->assertCount(1, $result->children);
    }

    public function testSplitReturnsNullForNonExistentPath(): void
    {
        $node = new TreeNode('/root', true);

        $result = $node->split('/root/nonexistent');

        $this->assertNull($result);
    }

    public function testSplitWithNestedPath(): void
    {
        $root = new TreeNode('/root', true);
        $dir1 = new TreeNode('/root/dir1', true);
        $dir2 = new TreeNode('/root/dir1/dir2', true);
        $file = new TreeNode('/root/dir1/dir2/file.txt', false);

        $dir2->addChild($file);
        $dir1->addChild($dir2);
        $root->addChild($dir1);

        $result = $root->split('dir1/dir2');

        $this->assertNotNull($result);
        $this->assertEquals('/root/dir1/dir2', $result->path);
        $this->assertCount(1, $result->children);
    }

    public function testMergeWithNonOverlappingChildren(): void
    {
        $node1 = new TreeNode('/root', true);
        $child1 = new TreeNode('/root/child1', false);
        $node1->addChild($child1);

        $node2 = new TreeNode('/root', true);
        $child2 = new TreeNode('/root/child2', false);
        $node2->addChild($child2);

        $result = $node1->merge($node2);

        $this->assertNotSame($node1, $result);
        $this->assertNotSame($node2, $result);
        $this->assertCount(2, $result->children);
        $this->assertArrayHasKey('child1', $result->children);
        $this->assertArrayHasKey('child2', $result->children);
    }

    public function testMergeWithOverlappingDirectories(): void
    {
        $node1 = new TreeNode('/root', true);
        $dir1 = new TreeNode('/root/shared', true);
        $file1 = new TreeNode('/root/shared/file1.txt', false);
        $dir1->addChild($file1);
        $node1->addChild($dir1);

        $node2 = new TreeNode('/root', true);
        $dir2 = new TreeNode('/root/shared', true);
        $file2 = new TreeNode('/root/shared/file2.txt', false);
        $dir2->addChild($file2);
        $node2->addChild($dir2);

        $result = $node1->merge($node2);

        $this->assertCount(1, $result->children);
        $this->assertArrayHasKey('shared', $result->children);
        $this->assertCount(2, $result->children['shared']->children);
        $this->assertArrayHasKey('file1.txt', $result->children['shared']->children);
        $this->assertArrayHasKey('file2.txt', $result->children['shared']->children);
    }

    public function testMergePreservesExistingFilesWhenConflicting(): void
    {
        $node1 = new TreeNode('/root', true);
        $file1 = new TreeNode('/root/file.txt', false);
        $node1->addChild($file1);

        $node2 = new TreeNode('/root', true);
        $file2 = new TreeNode('/root/file.txt', false);
        $node2->addChild($file2);

        $result = $node1->merge($node2);

        $this->assertCount(1, $result->children);
        // Should keep the original file, not add the second one
        $this->assertSame($file1->path, $result->children['file.txt']->path);
    }

    public function testAppendWithoutRename(): void
    {
        $parent = new TreeNode('/parent', true);
        $child = new TreeNode('/child', true);
        $grandchild = new TreeNode('/child/file.txt', false);
        $child->addChild($grandchild);

        $result = $parent->append($child);

        $this->assertNotSame($parent, $result);
        $this->assertCount(1, $result->children);
        $this->assertArrayHasKey('child', $result->children);
        $this->assertEquals('/child', $result->children['child']->path);
    }

    public function testAppendWithRename(): void
    {
        $parent = new TreeNode('/parent', true);
        $child = new TreeNode('/child', true);
        $grandchild = new TreeNode('/child/file.txt', false);
        $child->addChild($grandchild);

        $result = $parent->append($child, 'renamed');

        $this->assertCount(1, $result->children);
        $this->assertArrayHasKey('renamed', $result->children);
        $this->assertEquals('/parent/renamed', $result->children['renamed']->path);
        $this->assertEquals('renamed', $result->children['renamed']->name);
    }

    public function testAppendPreservesExistingChildren(): void
    {
        $parent = new TreeNode('/parent', true);
        $existing = new TreeNode('/parent/existing.txt', false);
        $parent->addChild($existing);

        $child = new TreeNode('/child', false);

        $result = $parent->append($child);

        $this->assertCount(2, $result->children);
        $this->assertArrayHasKey('existing.txt', $result->children);
        $this->assertArrayHasKey('child', $result->children);
    }

    public function testConstructorSetsNameFromPath(): void
    {
        $node = new TreeNode('/path/to/myfile.txt', false);

        $this->assertEquals('myfile.txt', $node->name);
        $this->assertEquals('/path/to/myfile.txt', $node->path);
        $this->assertFalse($node->isDir);
    }

    public function testConstructorDefaultsToDirectory(): void
    {
        $node = new TreeNode('/path/to/dir');

        $this->assertTrue($node->isDir);
    }
}
