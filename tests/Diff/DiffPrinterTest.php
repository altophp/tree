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

namespace Alto\Tree\Tests\Diff;

use Alto\Tree\Diff\DiffPrinter;
use Alto\Tree\Diff\DiffResult;
use Alto\Tree\TreeNode;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(DiffPrinter::class)]
class DiffPrinterTest extends TestCase
{
    private DiffPrinter $printer;

    protected function setUp(): void
    {
        $this->printer = new DiffPrinter();
    }

    public function testPrintEmptyDiff(): void
    {
        $diff = new DiffResult();

        $output = $this->printer->print($diff);

        $this->assertEquals('', $output);
    }

    public function testPrintWithAddedNodes(): void
    {
        $added = [
            'src/App.php' => new TreeNode('src/App.php', false),
        ];

        $diff = new DiffResult($added);
        $output = $this->printer->print($diff);

        $this->assertStringContainsString('+ src/App.php', $output);
    }

    public function testPrintWithRemovedNodes(): void
    {
        $removed = [
            'src/Old.php' => new TreeNode('src/Old.php', false),
        ];

        $diff = new DiffResult([], $removed);
        $output = $this->printer->print($diff);

        $this->assertStringContainsString('- src/Old.php', $output);
    }

    public function testPrintWithBothAddedAndRemoved(): void
    {
        $added = [
            'src/New.php' => new TreeNode('src/New.php', false),
        ];
        $removed = [
            'src/Old.php' => new TreeNode('src/Old.php', false),
        ];

        $diff = new DiffResult($added, $removed);
        $output = $this->printer->print($diff);

        $this->assertStringContainsString('+ src/New.php', $output);
        $this->assertStringContainsString('- src/Old.php', $output);
    }

    public function testPrintWithUnchangedNodes(): void
    {
        $added = [
            'src/New.php' => new TreeNode('src/New.php', false),
        ];
        $unchanged = [
            'src/App.php' => new TreeNode('src/App.php', false),
        ];

        $diff = new DiffResult($added, [], $unchanged);

        // By default, unchanged nodes are not shown
        $output = $this->printer->print($diff);
        $this->assertStringNotContainsString('src/App.php', $output);

        // With show_unchanged option
        $output = $this->printer->print($diff, ['show_unchanged' => true]);
        $this->assertStringContainsString('  src/App.php', $output);
    }

    public function testPrintSummary(): void
    {
        $added = [
            'src/Controller' => new TreeNode('src/Controller', true),
            'src/Controller/HomeController.php' => new TreeNode('src/Controller/HomeController.php', false),
        ];
        $removed = [
            'src/Model/User.php' => new TreeNode('src/Model/User.php', false),
        ];

        $diff = new DiffResult($added, $removed);
        $output = $this->printer->printSummary($diff);

        $this->assertStringContainsString('Diff Summary', $output);
        $this->assertStringContainsString('Added:', $output);
        $this->assertStringContainsString('Removed:', $output);
        $this->assertStringContainsString('src/Controller/HomeController.php', $output);
        $this->assertStringContainsString('src/Model/User.php', $output);
    }

    public function testPrintUnified(): void
    {
        $added = [
            'src/New.php' => new TreeNode('src/New.php', false),
        ];
        $removed = [
            'src/Old.php' => new TreeNode('src/Old.php', false),
        ];

        $diff = new DiffResult($added, $removed);
        $output = $this->printer->printUnified($diff);

        $this->assertStringContainsString('--- old', $output);
        $this->assertStringContainsString('+++ new', $output);
        $this->assertStringContainsString('- src/Old.php', $output);
        $this->assertStringContainsString('+ src/New.php', $output);
    }

    public function testPrintUnifiedWithCustomLabels(): void
    {
        $diff = new DiffResult();
        $output = $this->printer->printUnified($diff, 'version1', 'version2');

        $this->assertStringContainsString('--- version1', $output);
        $this->assertStringContainsString('+++ version2', $output);
    }
}
