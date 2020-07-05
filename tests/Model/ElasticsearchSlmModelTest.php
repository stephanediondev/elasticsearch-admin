<?php

namespace App\Tests\Model;

use App\Model\ElasticsearchSlmPolicyModel;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class ElasticsearchSlmModelTest extends WebTestCase
{
    public function test()
    {
        $policy = new ElasticsearchSlmPolicyModel();
        $policy->setName('name');
        $policy->setSnapshotName('snapshot-name');
        $policy->setRepository('repository');
        $policy->setSchedule('schedule');
        $policy->setExpireAfter('expire-after');

        $policy->setIgnoreUnavailable(true);
        $policy->setPartial(true);
        $policy->setIncludeGlobalState(true);

        $policy->setIndices([]);
        $policy->setLastSuccess([]);
        $policy->setLastFailure([]);
        $policy->setStats([]);

        $policy->setMinCount(1);
        $policy->setMaxCount(2);
        $policy->setVersion(3);
        $policy->setNextExecution(4);
        $policy->setModifiedDate(5);

        $this->assertEquals($policy->getName(), 'name');
        $this->assertEquals($policy->getSnapshotName(), 'snapshot-name');
        $this->assertEquals($policy->getRepository(), 'repository');
        $this->assertEquals($policy->getSchedule(), 'schedule');
        $this->assertEquals($policy->getExpireAfter(), 'expire-after');

        $this->assertEquals($policy->getIgnoreUnavailable(), true);
        $this->assertIsBool($policy->getIgnoreUnavailable());

        $this->assertEquals($policy->getPartial(), true);
        $this->assertIsBool($policy->getPartial());

        $this->assertEquals($policy->getIncludeGlobalState(), true);
        $this->assertIsBool($policy->getIncludeGlobalState());

        $this->assertEquals($policy->getIndices(), []);
        $this->assertIsArray($policy->getIndices());

        $this->assertEquals($policy->getLastSuccess(), []);
        $this->assertIsArray($policy->getLastSuccess());

        $this->assertEquals($policy->getLastFailure(), []);
        $this->assertIsArray($policy->getLastFailure());

        $this->assertEquals($policy->getStats(), []);
        $this->assertIsArray($policy->getStats());

        $this->assertEquals($policy->getMinCount(), 1);
        $this->assertIsInt($policy->getMinCount());

        $this->assertEquals($policy->getMaxCount(), 2);
        $this->assertIsInt($policy->getMaxCount());

        $this->assertEquals($policy->getVersion(), 3);
        $this->assertIsInt($policy->getVersion());

        $this->assertEquals($policy->getNextExecution(), 4);
        $this->assertIsInt($policy->getNextExecution());

        $this->assertEquals($policy->getModifiedDate(), 5);
        $this->assertIsInt($policy->getModifiedDate());

        $policy->setName('.name');
        $this->assertEquals($policy->isSystem(), true);
    }
}
