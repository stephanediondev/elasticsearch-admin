<?php

namespace App\Tests\Model;

use App\Model\ElasticsearchShardModel;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class ElasticsearchShardModelTest extends WebTestCase
{
    public function test()
    {
        $shard = new ElasticsearchShardModel();
        $shard->setNumber(1);
        $shard->setIndex('index');
        $shard->setPrimaryOrReplica('primary-or-replica');
        $shard->setState('state');
        $shard->setUnassignedReason('unassigned-reason');
        $shard->setDocuments(2);
        $shard->setSize(3);
        $shard->setNode('node');

        $this->assertEquals($shard->getNumber(), 1);
        $this->assertIsInt($shard->getNumber());

        $this->assertEquals($shard->getIndex(), 'index');
        $this->assertEquals($shard->getPrimaryOrReplica(), 'primary-or-replica');
        $this->assertEquals($shard->getState(), 'state');
        $this->assertEquals($shard->getUnassignedReason(), 'unassigned-reason');

        $this->assertEquals($shard->getDocuments(), 2);
        $this->assertIsInt($shard->getDocuments());

        $this->assertEquals($shard->getSize(), 3);
        $this->assertIsInt($shard->getSize());

        $this->assertEquals($shard->getNode(), 'node');

        $shard->setPrimaryOrReplica('p');
        $this->assertEquals($shard->isPrimary(), true);
        $this->assertEquals($shard->isReplica(), false);

        $shard->setPrimaryOrReplica('r');
        $this->assertEquals($shard->isReplica(), true);
        $this->assertEquals($shard->isPrimary(), false);
    }
}
