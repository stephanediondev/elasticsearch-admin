<?php

namespace App\Tests\Model;

use App\Model\ElasticsearchNodeModel;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class ElasticsearchNodeModelTest extends WebTestCase
{
    public function test()
    {
        $node = new ElasticsearchNodeModel();
        $node->setName('name');
        $node->setId('id');
        $node->setVersion('version');
        $node->setOs([]);
        $node->setRoles([]);
        $node->setSettings([]);
        $node->setPlugins([]);

        $this->assertEquals($node->getName(), 'name');

        $this->assertEquals($node->getId(), 'id');

        $this->assertEquals($node->getVersion(), 'version');

        $this->assertEquals($node->getOs(), []);
        $this->assertIsArray($node->getOs());

        $this->assertEquals($node->getRoles(), []);
        $this->assertIsArray($node->getRoles());

        $this->assertEquals($node->getSettings(), []);
        $this->assertIsArray($node->getSettings());

        $this->assertEquals($node->getPlugins(), []);
        $this->assertIsArray($node->getPlugins());
    }
}
