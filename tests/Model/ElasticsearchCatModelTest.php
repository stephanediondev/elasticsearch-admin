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
        $this->assertEquals($cat->getIndex(), 'index');
        $this->assertEquals($cat->getRepository(), 'repository');
        $this->assertEquals($cat->getAlias(), 'alias');
        $this->assertEquals($cat->getNode(), 'node');
        $this->assertEquals($cat->getHeaders(), 'headers');
        $this->assertEquals($cat->getSort(), 'sort');
    }
}
