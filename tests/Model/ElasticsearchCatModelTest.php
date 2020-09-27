<?php

namespace App\Tests\Model;

use App\Model\ElasticsearchCatModel;
use PHPUnit\Framework\TestCase;

class ElasticsearchCatModelTest extends TestCase
{
    public function test()
    {
        $cat = new ElasticsearchCatModel();
        $cat->setCommand('command');
        $cat->setIndex('index');
        $cat->setRepository('repository');
        $cat->setAlias('alias');
        $cat->setNode('node');
        $cat->setHeaders('headers');
        $cat->setSort('sort');

        $this->assertEquals($cat->getCommand(), 'command');
        $this->assertIsString($cat->getCommand());

        $this->assertEquals($cat->getIndex(), 'index');
        $this->assertIsString($cat->getIndex());

        $this->assertEquals($cat->getRepository(), 'repository');
        $this->assertIsString($cat->getRepository());

        $this->assertEquals($cat->getAlias(), 'alias');
        $this->assertIsString($cat->getAlias());

        $this->assertEquals($cat->getNode(), 'node');
        $this->assertIsString($cat->getNode());

        $this->assertEquals($cat->getHeaders(), 'headers');
        $this->assertIsString($cat->getHeaders());

        $this->assertEquals($cat->getSort(), 'sort');
        $this->assertIsString($cat->getSort());
    }
}
