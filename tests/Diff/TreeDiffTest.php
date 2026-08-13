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

namespace Alto\Tree\Tests\Diff;

use Alto\Tree\Diff\TreeDiff;
use Alto\Tree\TreeBuilder;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(TreeDiff::class)]
class TreeDiffTest extends TestCase
{
    public function testCompareIdenticalTrees(): void
    {
        $paths = ['project/src/Controller/HomeController.php', 'project/src/Model/User.php'];

        $tree1 = TreeBuilder::fromPaths($paths, 'project');
        $tree2 = TreeBuilder::fromPaths($paths, 'project');

        $diff = TreeDiff::compare($tree1, $tree2);

        $this->assertCount(0, $diff->getAdded());
        $this->assertCount(0, $diff->getRemoved());
        $this->assertFalse($diff->hasChanges());
        $this->assertEquals('No changes', $diff->getSummary());
    }

    public function testCompareWithAddedFiles(): void
    {
        $oldPaths = ['project/src/Controller/HomeController.php'];
        $newPaths = ['project/src/Controller/HomeController.php', 'project/src/Controller/UserController.php'];

        $oldTree = TreeBuilder::fromPaths($oldPaths, 'project');
        $newTree = TreeBuilder::fromPaths($newPaths, 'project');

        $diff = TreeDiff::compare($oldTree, $newTree);

        $this->assertCount(1, $diff->getAdded());
        $this->assertCount(0, $diff->getRemoved());
        $this->assertTrue($diff->hasChanges());
        $this->assertEquals('+1', $diff->getSummary());

        $added = $diff->getAdded();
        $this->assertArrayHasKey('project/src/Controller/UserController.php', $added);
    }

    public function testCompareWithRemovedFiles(): void
    {
        $oldPaths = ['project/src/Controller/HomeController.php', 'project/src/Controller/UserController.php'];
        $newPaths = ['project/src/Controller/HomeController.php'];

        $oldTree = TreeBuilder::fromPaths($oldPaths, 'project');
        $newTree = TreeBuilder::fromPaths($newPaths, 'project');

        $diff = TreeDiff::compare($oldTree, $newTree);

        $this->assertCount(0, $diff->getAdded());
        $this->assertCount(1, $diff->getRemoved());
        $this->assertTrue($diff->hasChanges());
        $this->assertEquals('-1', $diff->getSummary());

        $removed = $diff->getRemoved();
        $this->assertArrayHasKey('project/src/Controller/UserController.php', $removed);
    }

    public function testCompareWithBothAddedAndRemoved(): void
    {
        $oldPaths = [
            'project/src/Controller/HomeController.php',
            'project/src/Model/User.php',
        ];
        $newPaths = [
            'project/src/Controller/HomeController.php',
            'project/src/Model/Product.php',
            'project/src/Service/EmailService.php',
        ];

        $oldTree = TreeBuilder::fromPaths($oldPaths, 'project');
        $newTree = TreeBuilder::fromPaths($newPaths, 'project');

        $diff = TreeDiff::compare($oldTree, $newTree);

        $this->assertCount(3, $diff->getAdded()); // Product.php + Service dir + EmailService.php
        $this->assertCount(1, $diff->getRemoved()); // User.php
        $this->assertTrue($diff->hasChanges());
        $this->assertEquals('+3 -1', $diff->getSummary());
    }

    public function testCompareWithNestedStructure(): void
    {
        $oldPaths = [
            'project/src/Controller/HomeController.php',
            'project/src/Model/User.php',
        ];
        $newPaths = [
            'project/src/Controller/HomeController.php',
            'project/src/Controller/Admin/DashboardController.php',
            'project/src/Model/User.php',
        ];

        $oldTree = TreeBuilder::fromPaths($oldPaths, 'project');
        $newTree = TreeBuilder::fromPaths($newPaths, 'project');

        $diff = TreeDiff::compare($oldTree, $newTree);

        $this->assertCount(2, $diff->getAdded()); // Admin directory + DashboardController
        $this->assertCount(0, $diff->getRemoved());

        $added = $diff->getAdded();
        $this->assertArrayHasKey('project/src/Controller/Admin', $added);
        $this->assertArrayHasKey('project/src/Controller/Admin/DashboardController.php', $added);
    }

    public function testCompareWithRemovedDirectory(): void
    {
        $oldPaths = [
            'project/src/Controller/HomeController.php',
            'project/src/Model/User.php',
            'project/src/Model/Product.php',
        ];
        $newPaths = [
            'project/src/Controller/HomeController.php',
        ];

        $oldTree = TreeBuilder::fromPaths($oldPaths, 'project');
        $newTree = TreeBuilder::fromPaths($newPaths, 'project');

        $diff = TreeDiff::compare($oldTree, $newTree);

        $this->assertCount(0, $diff->getAdded());
        $this->assertCount(3, $diff->getRemoved()); // Model directory + 2 files

        $removed = $diff->getRemoved();
        $this->assertArrayHasKey('project/src/Model', $removed);
        $this->assertArrayHasKey('project/src/Model/User.php', $removed);
        $this->assertArrayHasKey('project/src/Model/Product.php', $removed);
    }

    public function testGetDetailedSummary(): void
    {
        $oldPaths = [
            'project/src/Controller/HomeController.php',
            'project/src/Model/User.php',
        ];
        $newPaths = [
            'project/src/Controller/HomeController.php',
            'project/src/Controller/UserController.php',
            'project/src/Service/EmailService.php',
        ];

        $oldTree = TreeBuilder::fromPaths($oldPaths, 'project');
        $newTree = TreeBuilder::fromPaths($newPaths, 'project');

        $diff = TreeDiff::compare($oldTree, $newTree);

        $summary = $diff->getDetailedSummary();

        $this->assertStringContainsString('Added', $summary);
        $this->assertStringContainsString('Removed', $summary);
        $this->assertStringContainsString('2 files', $summary);
        $this->assertStringContainsString('1 directory', $summary);
    }

    public function testGetUnchanged(): void
    {
        $oldPaths = [
            'project/src/Controller/HomeController.php',
            'project/src/Model/User.php',
        ];
        $newPaths = [
            'project/src/Controller/HomeController.php',
            'project/src/Model/User.php',
            'project/src/Model/Product.php',
        ];

        $oldTree = TreeBuilder::fromPaths($oldPaths, 'project');
        $newTree = TreeBuilder::fromPaths($newPaths, 'project');

        $diff = TreeDiff::compare($oldTree, $newTree);

        $unchanged = $diff->getUnchanged();

        $this->assertArrayHasKey('project/src/Controller', $unchanged);
        $this->assertArrayHasKey('project/src/Controller/HomeController.php', $unchanged);
        $this->assertArrayHasKey('project/src/Model', $unchanged);
        $this->assertArrayHasKey('project/src/Model/User.php', $unchanged);
    }

    public function testCompareEmptyTrees(): void
    {
        $tree1 = TreeBuilder::fromPaths([], 'project');
        $tree2 = TreeBuilder::fromPaths([], 'project');

        $diff = TreeDiff::compare($tree1, $tree2);

        $this->assertCount(0, $diff->getAdded());
        $this->assertCount(0, $diff->getRemoved());
        $this->assertFalse($diff->hasChanges());
    }

    public function testCompareOldEmptyNewWithFiles(): void
    {
        $oldTree = TreeBuilder::fromPaths([], 'project');
        $newTree = TreeBuilder::fromPaths(['project/src/App.php'], 'project');

        $diff = TreeDiff::compare($oldTree, $newTree);

        $this->assertCount(2, $diff->getAdded()); // src directory + App.php
        $this->assertCount(0, $diff->getRemoved());
        $this->assertTrue($diff->hasChanges());
    }

    public function testCompareOldWithFilesNewEmpty(): void
    {
        $oldTree = TreeBuilder::fromPaths(['project/src/App.php'], 'project');
        $newTree = TreeBuilder::fromPaths([], 'project');

        $diff = TreeDiff::compare($oldTree, $newTree);

        $this->assertCount(0, $diff->getAdded());
        $this->assertCount(2, $diff->getRemoved()); // src directory + App.php
        $this->assertTrue($diff->hasChanges());
    }
}
