<?php

namespace App\Tests\Model;

use App\Model\ElasticsearchReindexModel;
use PHPUnit\Framework\TestCase;

class ElasticsearchReindexModelTest extends TestCase
{
    public function test(): void
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
                'index' => $reindex->getSource(),
            ],
            'dest' => [
                'index' => $reindex->getDestination(),
            ],
        ]);
        $this->assertIsArray($reindex->getJson());
    }
}
