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

use Alto\Tree\Traverser\TreeTraverser;
use Alto\Tree\TreeNode;
use Alto\Tree\Visitor\VisitorInterface;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(TreeTraverser::class)]
class TreeTraverserTest extends TestCase
{
    public function testAddVisitorReturnsself(): void
    {
        $traverser = new TreeTraverser();
        $visitor = $this->createStub(VisitorInterface::class);

        $result = $traverser->addVisitor($visitor);

        $this->assertSame($traverser, $result);
    }

    public function testTraverseWithSingleNode(): void
    {
        $traverser = new TreeTraverser();
        $visitor = $this->createMock(VisitorInterface::class);
        $node = new TreeNode('/file.txt', false);

        $visitor->expects($this->once())
            ->method('visitNode')
            ->with($node, 0);

        $visitor->expects($this->never())
            ->method('enterDirectory');

        $visitor->expects($this->never())
            ->method('leaveDirectory');

        $traverser->addVisitor($visitor);
        $traverser->traverse($node);
    }

    public function testTraverseWithEmptyDirectory(): void
    {
        $traverser = new TreeTraverser();
        $visitor = $this->createMock(VisitorInterface::class);
        $node = new TreeNode('/dir', true);

        $visitor->expects($this->once())
            ->method('visitNode')
            ->with($node, 0);

        $visitor->expects($this->never())
            ->method('enterDirectory');

        $visitor->expects($this->never())
            ->method('leaveDirectory');

        $traverser->addVisitor($visitor);
        $traverser->traverse($node);
    }

    public function testTraverseWithDirectoryAndChildren(): void
    {
        $traverser = new TreeTraverser();
        $visitor = $this->createMock(VisitorInterface::class);

        $root = new TreeNode('/root', true);
        $child1 = new TreeNode('/root/child1.txt', false);
        $child2 = new TreeNode('/root/child2.txt', false);

        $root->addChild($child1);
        $root->addChild($child2);

        $callOrder = [];

        $visitor->expects($this->exactly(3))
            ->method('visitNode')
            ->willReturnCallback(function (TreeNode $node, int $depth) use (&$callOrder) {
                $callOrder[] = ['visitNode', $node->path, $depth];
            });

        $visitor->expects($this->once())
            ->method('enterDirectory')
            ->with($root, 0)
            ->willReturnCallback(function (TreeNode $node, int $depth) use (&$callOrder) {
                $callOrder[] = ['enterDirectory', $node->path, $depth];
            });

        $visitor->expects($this->once())
            ->method('leaveDirectory')
            ->with($root, 0)
            ->willReturnCallback(function (TreeNode $node, int $depth) use (&$callOrder) {
                $callOrder[] = ['leaveDirectory', $node->path, $depth];
            });

        $traverser->addVisitor($visitor);
        $traverser->traverse($root);

        // Verify the order of calls
        $this->assertEquals('visitNode', $callOrder[0][0]);
        $this->assertEquals('/root', $callOrder[0][1]);
        $this->assertEquals(0, $callOrder[0][2]);

        $this->assertEquals('enterDirectory', $callOrder[1][0]);

        // Children should be visited
        $this->assertContains(['visitNode', '/root/child1.txt', 1], $callOrder);
        $this->assertContains(['visitNode', '/root/child2.txt', 1], $callOrder);

        $this->assertEquals('leaveDirectory', $callOrder[4][0]);
    }

    public function testTraverseWithNestedDirectories(): void
    {
        $traverser = new TreeTraverser();
        $visitor = $this->createMock(VisitorInterface::class);

        $root = new TreeNode('/root', true);
        $subdir = new TreeNode('/root/subdir', true);
        $file = new TreeNode('/root/subdir/file.txt', false);

        $subdir->addChild($file);
        $root->addChild($subdir);

        $depths = [];

        $visitor->expects($this->exactly(3))
            ->method('visitNode')
            ->willReturnCallback(function (TreeNode $node, int $depth) use (&$depths) {
                $depths[$node->path] = $depth;
            });

        $traverser->addVisitor($visitor);
        $traverser->traverse($root);

        $this->assertEquals(0, $depths['/root']);
        $this->assertEquals(1, $depths['/root/subdir']);
        $this->assertEquals(2, $depths['/root/subdir/file.txt']);
    }

    public function testTraverseWithMultipleVisitors(): void
    {
        $traverser = new TreeTraverser();
        $visitor1 = $this->createMock(VisitorInterface::class);
        $visitor2 = $this->createMock(VisitorInterface::class);

        $node = new TreeNode('/file.txt', false);

        $visitor1->expects($this->once())
            ->method('visitNode')
            ->with($node, 0);

        $visitor2->expects($this->once())
            ->method('visitNode')
            ->with($node, 0);

        $traverser->addVisitor($visitor1);
        $traverser->addVisitor($visitor2);
        $traverser->traverse($node);
    }

    public function testTraverseComplexStructure(): void
    {
        $traverser = new TreeTraverser();
        $visitor = $this->createMock(VisitorInterface::class);

        $root = new TreeNode('/project', true);
        $src = new TreeNode('/project/src', true);
        $tests = new TreeNode('/project/tests', true);
        $file1 = new TreeNode('/project/src/file1.php', false);
        $file2 = new TreeNode('/project/src/file2.php', false);
        $test1 = new TreeNode('/project/tests/test1.php', false);

        $src->addChild($file1);
        $src->addChild($file2);
        $tests->addChild($test1);
        $root->addChild($src);
        $root->addChild($tests);

        $visitor->expects($this->exactly(6))
            ->method('visitNode');

        $visitor->expects($this->exactly(3))
            ->method('enterDirectory');

        $visitor->expects($this->exactly(3))
            ->method('leaveDirectory');

        $traverser->addVisitor($visitor);
        $traverser->traverse($root);
    }
}
