<?php

namespace App\Tests\Model;

use App\Model\ElasticsearchClusterSettingModel;
use PHPUnit\Framework\TestCase;

class ElasticsearchClusterSettingModelTest extends TestCase
{
    public function test(): void
    {
        $clusterSettingModel = new ElasticsearchClusterSettingModel();
        $clusterSettingModel->setType('type');
        $clusterSettingModel->setSetting('setting');
        $clusterSettingModel->setValue('value');

        $this->assertEquals($clusterSettingModel->getType(), 'type');
        $this->assertIsString($clusterSettingModel->getType());

        $this->assertEquals($clusterSettingModel->getSetting(), 'setting');
        $this->assertEquals(strval($clusterSettingModel), 'setting');
        $this->assertIsString($clusterSettingModel->getSetting());

        $this->assertEquals($clusterSettingModel->getValue(), 'value');
        $this->assertIsString($clusterSettingModel->getValue());

        $this->assertEquals($clusterSettingModel->getJson(), [
            $clusterSettingModel->getType() => [
                $clusterSettingModel->getSetting() => $clusterSettingModel->getValue(),
            ],
        ]);
        $this->assertIsArray($clusterSettingModel->getJson());
    }
}
