<?php

namespace App\Tests\Model;

use App\Model\ElasticsearchIndexModel;
use PHPUnit\Framework\TestCase;

class ElasticsearchIndexModelTest extends TestCase
{
    public function test(): void
    {
        $index = new ElasticsearchIndexModel();
        $index->setName('name');
        $index->setStatus('status');
        $index->setHealth('health');
        $index->setFrozen('frozen');
        $index->setPrimaryShards(5);
        $index->setReplicas(2);
        $index->setDocuments(3);
        $index->setDocumentsDeleted(4);
        $index->setPrimarySize(5);
        $index->setTotalSize(6);
        $index->setCreationDate('creation-date');
        $index->setSettings([]);
        $index->setSettingsJson(json_encode([]));
        $index->setSetting('setting-key', 'setting-value');
        $index->setMappings(['mappings']);
        $index->setMappingsJson(json_encode(['mappings']));
        $index->setMappingsFlat(['mapping-field' => ['type' => 'mapping-type']]);
        $index->setAliases(['aliases']);
        $index->setAliasesJson(json_encode(['aliases']));

        $this->assertEquals($index->getName(), 'name');
        $this->assertEquals(strval($index), 'name');
        $this->assertIsString($index->getName());

        $this->assertEquals($index->getStatus(), 'status');
        $this->assertIsString($index->getStatus());

        $this->assertEquals($index->getHealth(), 'health');
        $this->assertIsString($index->getHealth());

        $this->assertEquals($index->getFrozen(), 'frozen');
        $this->assertIsString($index->getFrozen());

        $this->assertEquals($index->getPrimaryShards(), 5);
        $this->assertIsInt($index->getPrimaryShards());

        $this->assertEquals($index->getReplicas(), 2);
        $this->assertIsInt($index->getReplicas());

        $this->assertEquals($index->getShards(), 15);
        $this->assertIsInt($index->getShards());

        $this->assertEquals($index->getDocuments(), 3);
        $this->assertIsInt($index->getDocuments());

        $this->assertEquals($index->getDocumentsDeleted(), 4);
        $this->assertIsInt($index->getDocumentsDeleted());

        $this->assertEquals($index->getPrimarySize(), 5);
        $this->assertIsInt($index->getPrimarySize());

        $this->assertEquals($index->getTotalSize(), 6);
        $this->assertIsInt($index->getTotalSize());

        $this->assertEquals($index->getCreationDate(), 'creation-date');
        $this->assertIsString($index->getCreationDate());

        $this->assertEquals($index->getSettings(), ['setting-key' => 'setting-value']);
        $this->assertIsArray($index->getSettings());

        $this->assertEquals($index->getSetting('setting-key'), 'setting-value');
        $this->assertIsString($index->getSetting('setting-key'));

        $this->assertEquals($index->getMappings(), ['mappings']);
        $this->assertIsArray($index->getMappings());

        $this->assertEquals($index->getMappingsFlat(), ['mapping-field' => ['type' => 'mapping-type']]);
        $this->assertIsArray($index->getMappingsFlat());

        $this->assertEquals($index->isSystem(), false);
        $this->assertIsBool($index->isSystem());

        $this->assertEquals($index->hasMappingType('mapping-type'), true);
        $this->assertEquals($index->hasMappingType('mapping-fail'), false);

        $this->assertEquals($index->getAliases(), ['aliases']);
        $this->assertIsArray($index->getAliases());

        $index->setName('.name');
        $this->assertEquals($index->isSystem(), true);
        $this->assertIsBool($index->isSystem());
    }
}
