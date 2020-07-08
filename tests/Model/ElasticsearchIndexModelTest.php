<?php

namespace App\Tests\Model;

use App\Model\ElasticsearchIndexModel;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class ElasticsearchIndexModelTest extends WebTestCase
{
    public function test()
    {
        $index = new ElasticsearchIndexModel();
        $index->setName('name');
        $index->setStatus('status');
        $index->setHealth('health');
        $index->setFrozen('frozen');
        $index->setShards(1);
        $index->setReplicas(2);
        $index->setDocuments(3);
        $index->setDocumentsDeleted(4);
        $index->setPrimarySize(5);
        $index->setTotalSize(6);
        $index->setCreationDate('creation-date');
        $index->setSettings('');
        $index->setSettings([]);
        $index->setSetting('setting-key', 'setting-value');
        $index->setMappings('');
        $index->setMappings([]);
        $index->setMappingsFlat(['mapping-field' => ['type' => 'mapping-type']]);

        $this->assertEquals($index->getName(), 'name');
        $this->assertEquals($index->getStatus(), 'status');
        $this->assertEquals($index->getHealth(), 'health');
        $this->assertEquals($index->getFrozen(), 'frozen');

        $this->assertEquals($index->getShards(), 1);
        $this->assertIsInt($index->getShards());

        $this->assertEquals($index->getReplicas(), 2);
        $this->assertIsInt($index->getReplicas());

        $this->assertEquals($index->getDocuments(), 3);
        $this->assertIsInt($index->getDocuments());

        $this->assertEquals($index->getDocumentsDeleted(), 4);
        $this->assertIsInt($index->getDocumentsDeleted());

        $this->assertEquals($index->getPrimarySize(), 5);
        $this->assertIsInt($index->getPrimarySize());

        $this->assertEquals($index->getTotalSize(), 6);
        $this->assertIsInt($index->getTotalSize());

        $this->assertEquals($index->getCreationDate(), 'creation-date');

        $this->assertEquals($index->getSettings(), ['setting-key' => 'setting-value']);
        $this->assertIsArray($index->getSettings());
        $this->assertEquals($index->getSetting('setting-key'), 'setting-value');

        $this->assertEquals($index->getMappings(), []);
        $this->assertIsArray($index->getMappings());

        $this->assertEquals($index->getMappingsFlat(), ['mapping-field' => ['type' => 'mapping-type']]);
        $this->assertIsArray($index->getMappingsFlat());

        $this->assertEquals($index->isSystem(), false);

        $this->assertEquals($index->hasMappingType('mapping-type'), true);
        $this->assertEquals($index->hasMappingType('mapping-fail'), false);

        $index->setName('.name');
        $this->assertEquals($index->isSystem(), true);
    }
}
