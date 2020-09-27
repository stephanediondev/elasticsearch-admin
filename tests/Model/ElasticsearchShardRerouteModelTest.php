<?php

namespace App\Tests\Model;

use App\Model\ElasticsearchShardRerouteModel;
use PHPUnit\Framework\TestCase;

class ElasticsearchShardRerouteModelTest extends TestCase
{
    public function test()
    {
        $shardRerouteModel = new ElasticsearchShardRerouteModel();
        $shardRerouteModel->setNumber(1);
        $shardRerouteModel->setIndex('index');
        $shardRerouteModel->setCommand('command');
        $shardRerouteModel->setState('state');
        $shardRerouteModel->setNode('node');
        $shardRerouteModel->setToNode('no-node');

        $this->assertEquals($shardRerouteModel->getNumber(), 1);
        $this->assertIsNumeric($shardRerouteModel->getNumber());

        $this->assertEquals($shardRerouteModel->getIndex(), 'index');
        $this->assertIsString($shardRerouteModel->getIndex());

        $this->assertEquals($shardRerouteModel->getCommand(), 'command');
        $this->assertIsString($shardRerouteModel->getCommand());

        $this->assertEquals($shardRerouteModel->getNode(), 'node');
        $this->assertIsString($shardRerouteModel->getNode());

        $this->assertEquals($shardRerouteModel->getToNode(), 'no-node');
        $this->assertIsString($shardRerouteModel->getToNode());
    }
}
