<?php

namespace App\Tests\Model;

use App\Model\ElasticsearchClusterDiskThresholdsModel;
use PHPUnit\Framework\TestCase;

class ElasticsearchClusterDiskThresholdsModelTest extends TestCase
{
    public function test()
    {
        $clusterDiskThresholdsModel = new ElasticsearchClusterDiskThresholdsModel();
        $clusterDiskThresholdsModel->setEnabled(true);
        $clusterDiskThresholdsModel->setLow('low');
        $clusterDiskThresholdsModel->setHigh('high');
        $clusterDiskThresholdsModel->setFloodStage('flood-stage');

        $this->assertEquals($clusterDiskThresholdsModel->getEnabled(), true);
        $this->assertIsBool($clusterDiskThresholdsModel->getEnabled());

        $this->assertEquals($clusterDiskThresholdsModel->getLow(), 'low');
        $this->assertIsString($clusterDiskThresholdsModel->getLow());

        $this->assertEquals($clusterDiskThresholdsModel->getHigh(), 'high');
        $this->assertIsString($clusterDiskThresholdsModel->getHigh());

        $this->assertEquals($clusterDiskThresholdsModel->getFloodStage(), 'flood-stage');
        $this->assertIsString($clusterDiskThresholdsModel->getFloodStage());

        $this->assertEquals($clusterDiskThresholdsModel->getJson(), [
            'persistent' => [
                'cluster.routing.allocation.disk.threshold_enabled' => $clusterDiskThresholdsModel->getEnabled(),
                'cluster.routing.allocation.disk.watermark.low' => $clusterDiskThresholdsModel->getLow(),
                'cluster.routing.allocation.disk.watermark.high' => $clusterDiskThresholdsModel->getHigh(),
                'cluster.routing.allocation.disk.watermark.flood_stage' => $clusterDiskThresholdsModel->getFloodStage(),
            ],
        ]);
        $this->assertIsArray($clusterDiskThresholdsModel->getJson());
    }
}
