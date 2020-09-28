<?php

namespace App\Tests\Model;

use App\Model\ElasticsearchIndexSettingModel;
use PHPUnit\Framework\TestCase;

class ElasticsearchIndexSettingModelTest extends TestCase
{
    public function test()
    {
        $indexSettingModel = new ElasticsearchIndexSettingModel();
        $indexSettingModel->setName('name');
        $indexSettingModel->setValue('value');

        $this->assertEquals($indexSettingModel->getName(), 'name');
        $this->assertEquals(strval($indexSettingModel), 'name');
        $this->assertIsString($indexSettingModel->getName());

        $this->assertEquals($indexSettingModel->getValue(), 'value');
        $this->assertIsString($indexSettingModel->getValue());

        $this->assertEquals($indexSettingModel->getJson(), [
            $indexSettingModel->getName() => $indexSettingModel->getValue(),
        ]);
        $this->assertIsArray($indexSettingModel->getJson());
    }
}
