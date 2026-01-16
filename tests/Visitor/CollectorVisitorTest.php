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

namespace Alto\Tree\Tests\Visitor;

use Alto\Tree\TreeNode;
use Alto\Tree\Visitor\CollectorVisitor;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(CollectorVisitor::class)]
class CollectorVisitorTest extends TestCase
{
    public function testInitialState(): void
    {
        $visitor = new CollectorVisitor();

        $this->assertSame([], $visitor->getFiles());
        $this->assertSame([], $visitor->getDirectories());
        $this->assertEquals(0, $visitor->getTotalFiles());
        $this->assertEquals(0, $visitor->getTotalDirectories());
    }

    public function testVisitNodeWithFile(): void
    {
        $visitor = new CollectorVisitor();
        $file = new TreeNode('/path/to/file.txt', false);

        $visitor->visitNode($file, 0);

        $this->assertCount(1, $visitor->getFiles());
        $this->assertContains('/path/to/file.txt', $visitor->getFiles());
        $this->assertEquals(1, $visitor->getTotalFiles());
        $this->assertSame([], $visitor->getDirectories());
        $this->assertEquals(0, $visitor->getTotalDirectories());
    }

    public function testVisitNodeWithDirectory(): void
    {
        $visitor = new CollectorVisitor();
        $dir = new TreeNode('/path/to/dir', true);

        $visitor->visitNode($dir, 0);

        $this->assertSame([], $visitor->getFiles());
        $this->assertEquals(0, $visitor->getTotalFiles());
        $this->assertSame([], $visitor->getDirectories());
        $this->assertEquals(0, $visitor->getTotalDirectories());
    }

    public function testEnterDirectoryAddsToDirectories(): void
    {
        $visitor = new CollectorVisitor();
        $dir = new TreeNode('/path/to/dir', true);

        $visitor->enterDirectory($dir, 0);

        $this->assertCount(1, $visitor->getDirectories());
        $this->assertContains('/path/to/dir', $visitor->getDirectories());
        $this->assertEquals(1, $visitor->getTotalDirectories());
    }

    public function testLeaveDirectoryDoesNothing(): void
    {
        $visitor = new CollectorVisitor();
        $dir = new TreeNode('/path/to/dir', true);

        $visitor->enterDirectory($dir, 0);
        $before = $visitor->getTotalDirectories();

        $visitor->leaveDirectory($dir, 0);
        $after = $visitor->getTotalDirectories();

        $this->assertEquals($before, $after);
    }

    public function testCollectMultipleFilesAndDirectories(): void
    {
        $visitor = new CollectorVisitor();

        $dir1 = new TreeNode('/root', true);
        $dir2 = new TreeNode('/root/subdir', true);
        $file1 = new TreeNode('/root/file1.txt', false);
        $file2 = new TreeNode('/root/subdir/file2.txt', false);

        $visitor->enterDirectory($dir1, 0);
        $visitor->visitNode($file1, 1);
        $visitor->enterDirectory($dir2, 1);
        $visitor->visitNode($file2, 2);

        $this->assertCount(2, $visitor->getFiles());
        $this->assertContains('/root/file1.txt', $visitor->getFiles());
        $this->assertContains('/root/subdir/file2.txt', $visitor->getFiles());
        $this->assertEquals(2, $visitor->getTotalFiles());

        $this->assertCount(2, $visitor->getDirectories());
        $this->assertContains('/root', $visitor->getDirectories());
        $this->assertContains('/root/subdir', $visitor->getDirectories());
        $this->assertEquals(2, $visitor->getTotalDirectories());
    }

    public function testGetSummaryWithNoItems(): void
    {
        $visitor = new CollectorVisitor();

        $summary = $visitor->getSummary();

        $this->assertEquals('Found 0 files and 0 directories', $summary);
    }

    public function testGetSummaryWithItems(): void
    {
        $visitor = new CollectorVisitor();

        $file1 = new TreeNode('/file1.txt', false);
        $file2 = new TreeNode('/file2.txt', false);
        $dir1 = new TreeNode('/dir1', true);
        $dir2 = new TreeNode('/dir2', true);
        $dir3 = new TreeNode('/dir3', true);

        $visitor->visitNode($file1, 0);
        $visitor->visitNode($file2, 0);
        $visitor->enterDirectory($dir1, 0);
        $visitor->enterDirectory($dir2, 0);
        $visitor->enterDirectory($dir3, 0);

        $summary = $visitor->getSummary();

        $this->assertEquals('Found 2 files and 3 directories', $summary);
    }

    public function testGetSummaryWithOnlyFiles(): void
    {
        $visitor = new CollectorVisitor();
        $file = new TreeNode('/file.txt', false);

        $visitor->visitNode($file, 0);

        $summary = $visitor->getSummary();

        $this->assertEquals('Found 1 files and 0 directories', $summary);
    }

    public function testGetSummaryWithOnlyDirectories(): void
    {
        $visitor = new CollectorVisitor();
        $dir = new TreeNode('/dir', true);

        $visitor->enterDirectory($dir, 0);

        $summary = $visitor->getSummary();

        $this->assertEquals('Found 0 files and 1 directories', $summary);
    }
}
