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

namespace Alto\Tree\Tests\Provider;

use Alto\Tree\Provider\NodeData;
use Alto\Tree\Provider\PathsProvider;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(PathsProvider::class)]
#[CoversClass(NodeData::class)]
class PathsProviderTest extends TestCase
{
    public function testGetRootPath(): void
    {
        $provider = new PathsProvider(['src/App.php'], 'project');

        $this->assertEquals('project', $provider->getRootPath());
    }

    public function testGetNodesWithSingleFile(): void
    {
        $provider = new PathsProvider(['src/App.php'], 'project');
        $nodes = $provider->getNodes();

        $this->assertCount(2, $nodes); // src directory + App.php

        $this->assertEquals('src', $nodes[0]->path);
        $this->assertTrue($nodes[0]->isDir);

        $this->assertEquals('src/App.php', $nodes[1]->path);
        $this->assertFalse($nodes[1]->isDir);
    }

    public function testGetNodesWithMultipleFiles(): void
    {
        $provider = new PathsProvider([
            'src/Controller/HomeController.php',
            'src/Model/User.php',
        ], 'project');

        $nodes = $provider->getNodes();

        // Should have: src, Controller, HomeController.php, Model, User.php
        $this->assertCount(5, $nodes);
    }

    public function testGetNodesAvoidsDuplicates(): void
    {
        $provider = new PathsProvider([
            'src/App.php',
            'src/Router.php',  // Both in src/
        ], 'project');

        $nodes = $provider->getNodes();

        // Should have: src, App.php, Router.php (src only once)
        $this->assertCount(3, $nodes);

        $paths = array_map(fn ($n) => $n->path, $nodes);
        $this->assertEquals(['src', 'src/App.php', 'src/Router.php'], $paths);
    }

    public function testDefaultRootPath(): void
    {
        $provider = new PathsProvider(['App.php']);

        $this->assertEquals('src', $provider->getRootPath());
    }

    public function testDetectsDirectoriesAndFiles(): void
    {
        $provider = new PathsProvider([
            'config/app.php',
            'config/routes',  // No extension, treated as directory
        ], 'project');

        $nodes = $provider->getNodes();

        $nodesByPath = [];
        foreach ($nodes as $node) {
            $nodesByPath[$node->path] = $node;
        }

        $this->assertTrue($nodesByPath['config']->isDir);
        $this->assertFalse($nodesByPath['config/app.php']->isDir);
        $this->assertTrue($nodesByPath['config/routes']->isDir);
    }
}
