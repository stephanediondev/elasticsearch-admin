<?php

namespace App\Tests\Model;

use App\Model\ElasticsearchIndexAliasModel;
use PHPUnit\Framework\TestCase;

class ElasticsearchIndexAliasModelTest extends TestCase
{
    public function test(): void
    {
        $indexAliasModel = new ElasticsearchIndexAliasModel();
        $indexAliasModel->setName('name');

        $this->assertEquals($indexAliasModel->getName(), 'name');
        $this->assertEquals(strval($indexAliasModel), 'name');
        $this->assertIsString($indexAliasModel->getName());
    }
}
