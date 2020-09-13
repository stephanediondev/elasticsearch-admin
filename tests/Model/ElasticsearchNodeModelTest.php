<?php

namespace App\Tests\Model;

use App\Model\ElasticsearchNodeModel;
use PHPUnit\Framework\TestCase;

class ElasticsearchNodeModelTest extends TestCase
{
    public function test()
    {
        $node = new ElasticsearchNodeModel();
        $node->setName('name');
        $node->setId('id');
        $node->setVersion('version');
        $node->setOs(['os']);
        $node->setRoles(['roles']);
        $node->setSettings(['settings']);
        $node->setPlugins(['plugins']);

        $this->assertEquals($node->getName(), 'name');
        $this->assertEquals(strval($node), 'name');

        $this->assertEquals($node->getId(), 'id');

        $this->assertEquals($node->getVersion(), 'version');

        $this->assertEquals($node->getOs(), ['os']);
        $this->assertIsArray($node->getOs());

        $this->assertEquals($node->getRoles(), ['roles']);
        $this->assertIsArray($node->getRoles());

        $this->assertEquals($node->getSettings(), ['settings']);
        $this->assertIsArray($node->getSettings());

        $this->assertEquals($node->getPlugins(), ['plugins']);
        $this->assertIsArray($node->getPlugins());
    }
}
