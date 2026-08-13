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
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(Tree::class)]
class TreeTest extends TestCase
{
    public function testConstructor(): void
    {
        $path = '/root/path';
        $tree = new Tree($path);

        $this->assertEquals($path, $tree->path);
        $this->assertEquals('path', $tree->name);
        $this->assertTrue($tree->isDir);
        $this->assertCount(0, $tree->children);
    }

    public function testToString(): void
    {
        $path = '/root/path';
        $tree = new Tree($path);
        $expected = $path . "\n" . str_repeat('=', strlen($path)) . "\n";

        $this->assertEquals($expected, (string) $tree);
    }

    public function testTreeIsAlwaysDirectory(): void
    {
        $tree = new Tree('/any/path');

        $this->assertTrue($tree->isDir);
    }

    public function testTreeInheritsTreeNodeBehavior(): void
    {
        $tree = new Tree('/root');
        $child = new Tree('/root/child');

        $tree->addChild($child);

        $this->assertCount(1, $tree->children);
        $this->assertArrayHasKey('child', $tree->children);
    }
}
