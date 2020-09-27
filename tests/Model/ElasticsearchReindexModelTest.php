<?php

namespace App\Tests\Model;

use App\Model\ElasticsearchReindexModel;
use PHPUnit\Framework\TestCase;

class ElasticsearchReindexModelTest extends TestCase
{
    public function test()
    {
        $reindex = new ElasticsearchReindexModel();
        $reindex->setSource('source');
        $reindex->setDestination('destination');

        $this->assertEquals($reindex->getSource(), 'source');
        $this->assertIsString($reindex->getSource());

        $this->assertEquals($reindex->getDestination(), 'destination');
        $this->assertIsString($reindex->getDestination());

        $this->assertEquals($reindex->getJson(), [
            'source' => [
                'index' => 'source',
            ],
            'dest' => [
                'index' => 'destination',
            ],
        ]);
        $this->assertIsArray($reindex->getJson());
    }
}
