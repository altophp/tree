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

use Alto\Tree\Diff\DiffResult;
use Alto\Tree\TreeNode;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(DiffResult::class)]
class DiffResultTest extends TestCase
{
    public function testEmptyDiffResult(): void
    {
        $diff = new DiffResult();

        $this->assertCount(0, $diff->getAdded());
        $this->assertCount(0, $diff->getRemoved());
        $this->assertCount(0, $diff->getUnchanged());
        $this->assertFalse($diff->hasChanges());
        $this->assertEquals('No changes', $diff->getSummary());
    }

    public function testDiffResultWithAddedNodes(): void
    {
        $added = [
            'src/App.php' => new TreeNode('src/App.php', false),
        ];

        $diff = new DiffResult($added);

        $this->assertCount(1, $diff->getAdded());
        $this->assertCount(0, $diff->getRemoved());
        $this->assertTrue($diff->hasChanges());
        $this->assertEquals('+1', $diff->getSummary());
    }

    public function testDiffResultWithRemovedNodes(): void
    {
        $removed = [
            'src/App.php' => new TreeNode('src/App.php', false),
        ];

        $diff = new DiffResult([], $removed);

        $this->assertCount(0, $diff->getAdded());
        $this->assertCount(1, $diff->getRemoved());
        $this->assertTrue($diff->hasChanges());
        $this->assertEquals('-1', $diff->getSummary());
    }

    public function testDiffResultWithBothAddedAndRemoved(): void
    {
        $added = [
            'src/New.php' => new TreeNode('src/New.php', false),
        ];
        $removed = [
            'src/Old.php' => new TreeNode('src/Old.php', false),
        ];

        $diff = new DiffResult($added, $removed);

        $this->assertCount(1, $diff->getAdded());
        $this->assertCount(1, $diff->getRemoved());
        $this->assertTrue($diff->hasChanges());
        $this->assertEquals('+1 -1', $diff->getSummary());
    }

    public function testDiffResultWithUnchanged(): void
    {
        $unchanged = [
            'src/App.php' => new TreeNode('src/App.php', false),
        ];

        $diff = new DiffResult([], [], $unchanged);

        $this->assertCount(0, $diff->getAdded());
        $this->assertCount(0, $diff->getRemoved());
        $this->assertCount(1, $diff->getUnchanged());
        $this->assertFalse($diff->hasChanges());
    }

    public function testGetCounts(): void
    {
        $added = [
            'src/New1.php' => new TreeNode('src/New1.php', false),
            'src/New2.php' => new TreeNode('src/New2.php', false),
        ];
        $removed = [
            'src/Old.php' => new TreeNode('src/Old.php', false),
        ];
        $unchanged = [
            'src/App.php' => new TreeNode('src/App.php', false),
        ];

        $diff = new DiffResult($added, $removed, $unchanged);

        $this->assertEquals(2, $diff->getAddedCount());
        $this->assertEquals(1, $diff->getRemovedCount());
        $this->assertEquals(1, $diff->getUnchangedCount());
    }

    public function testGetDetailedSummary(): void
    {
        $added = [
            'src/Controller' => new TreeNode('src/Controller', true),
            'src/Controller/HomeController.php' => new TreeNode('src/Controller/HomeController.php', false),
        ];
        $removed = [
            'src/Model' => new TreeNode('src/Model', true),
            'src/Model/User.php' => new TreeNode('src/Model/User.php', false),
            'src/Model/Product.php' => new TreeNode('src/Model/Product.php', false),
        ];

        $diff = new DiffResult($added, $removed);

        $summary = $diff->getDetailedSummary();

        $this->assertStringContainsString('Added:', $summary);
        $this->assertStringContainsString('1 file', $summary);
        $this->assertStringContainsString('1 directory', $summary);
        $this->assertStringContainsString('Removed:', $summary);
        $this->assertStringContainsString('2 files', $summary);
    }

    public function testGetDetailedSummaryNoChanges(): void
    {
        $diff = new DiffResult();

        $this->assertEquals('No changes', $diff->getDetailedSummary());
    }

    public function testGetDetailedSummaryOnlyFiles(): void
    {
        $added = [
            'src/App.php' => new TreeNode('src/App.php', false),
        ];

        $diff = new DiffResult($added);

        $summary = $diff->getDetailedSummary();

        $this->assertStringContainsString('Added:', $summary);
        $this->assertStringContainsString('1 file', $summary);
        $this->assertStringNotContainsString('directory', $summary);
    }

    public function testGetDetailedSummaryOnlyDirectories(): void
    {
        $removed = [
            'src/Model' => new TreeNode('src/Model', true),
        ];

        $diff = new DiffResult([], $removed);

        $summary = $diff->getDetailedSummary();

        $this->assertStringContainsString('Removed:', $summary);
        $this->assertStringContainsString('1 directory', $summary);
        $this->assertStringNotContainsString('file', $summary);
    }
}
