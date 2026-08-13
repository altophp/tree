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

namespace Alto\Tree\Tests\Visitor;

use Alto\Tree\TreeNode;
use Alto\Tree\Visitor\FlattenVisitor;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(FlattenVisitor::class)]
class FlattenVisitorTest extends TestCase
{
    public function testGetPathsInitiallyEmpty(): void
    {
        $visitor = new FlattenVisitor();

        $this->assertSame([], $visitor->getPaths());
    }

    public function testVisitNodeAddsPath(): void
    {
        $visitor = new FlattenVisitor();
        $node = new TreeNode('/path/to/node', true);

        $visitor->visitNode($node, 0);

        $paths = $visitor->getPaths();
        $this->assertCount(1, $paths);
        $this->assertContains('/path/to/node', $paths);
    }

    public function testVisitMultipleNodes(): void
    {
        $visitor = new FlattenVisitor();
        $node1 = new TreeNode('/path/to/node1', true);
        $node2 = new TreeNode('/path/to/node2', false);
        $node3 = new TreeNode('/path/to/node3', true);

        $visitor->visitNode($node1, 0);
        $visitor->visitNode($node2, 1);
        $visitor->visitNode($node3, 0);

        $paths = $visitor->getPaths();
        $this->assertCount(3, $paths);
        $this->assertContains('/path/to/node1', $paths);
        $this->assertContains('/path/to/node2', $paths);
        $this->assertContains('/path/to/node3', $paths);
    }

    public function testVisitNodeAddsFilesAndDirectories(): void
    {
        $visitor = new FlattenVisitor();
        $dir = new TreeNode('/path/to/dir', true);
        $file = new TreeNode('/path/to/file.txt', false);

        $visitor->visitNode($dir, 0);
        $visitor->visitNode($file, 1);

        $paths = $visitor->getPaths();
        $this->assertCount(2, $paths);
        $this->assertContains('/path/to/dir', $paths);
        $this->assertContains('/path/to/file.txt', $paths);
    }

    public function testEnterDirectoryDoesNothing(): void
    {
        $visitor = new FlattenVisitor();
        $node = new TreeNode('/path/to/dir', true);

        $visitor->enterDirectory($node, 0);

        $this->assertSame([], $visitor->getPaths());
    }

    public function testLeaveDirectoryDoesNothing(): void
    {
        $visitor = new FlattenVisitor();
        $node = new TreeNode('/path/to/dir', true);

        $visitor->leaveDirectory($node, 0);

        $this->assertSame([], $visitor->getPaths());
    }

    public function testPathsAreInOrderOfVisitation(): void
    {
        $visitor = new FlattenVisitor();
        $node1 = new TreeNode('/first', true);
        $node2 = new TreeNode('/second', true);
        $node3 = new TreeNode('/third', false);

        $visitor->visitNode($node1, 0);
        $visitor->visitNode($node2, 0);
        $visitor->visitNode($node3, 0);

        $paths = $visitor->getPaths();
        $this->assertEquals(['/first', '/second', '/third'], $paths);
    }
}
