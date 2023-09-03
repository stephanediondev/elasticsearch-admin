<?php

namespace App\Tests\Model;

use App\Model\ElasticsearchIndexTemplateModel;
use PHPUnit\Framework\TestCase;

class ElasticsearchIndexTemplateModelTest extends TestCase
{
    public function test(): void
    {
        $template = new ElasticsearchIndexTemplateModel();
        $template->setName('name');
        $template->setIndexPatterns('index-patterns');
        $template->setVersion(1);
        $template->setPriority(2);
        $template->setSettings([]);
        if ($json = json_encode([])) {
            $template->setSettingsJson($json);
        }
        $template->setSetting('setting-key', 'setting-value');
        $template->setMappings(['mappings']);
        if ($json = json_encode(['mappings'])) {
            $template->setMappingsJson($json);
        }
        $template->setAliases(['aliases']);
        if ($json = json_encode(['aliases'])) {
            $template->setAliasesJson($json);
        }
        $template->setMetadata(['metadata']);
        if ($json = json_encode(['metadata'])) {
            $template->setMetadataJson($json);
        }
        $template->setComposedOf(['composedof']);
        $template->setDataStream(true);

        $this->assertEquals($template->getName(), 'name');
        $this->assertEquals(strval($template), 'name');
        $this->assertIsString($template->getName());

        $this->assertEquals($template->getIndexPatterns(), 'index-patterns');
        $this->assertIsString($template->getIndexPatterns());

        $this->assertEquals($template->getVersion(), 1);
        $this->assertIsInt($template->getVersion());

        $this->assertEquals($template->getPriority(), 2);
        $this->assertIsInt($template->getPriority());

        $this->assertEquals($template->getSettings(), ['setting-key' => 'setting-value']);
        $this->assertIsArray($template->getSettings());

        $this->assertEquals($template->getSetting('setting-key'), 'setting-value');
        $this->assertIsString($template->getSetting('setting-key'));

        $this->assertEquals($template->getMappings(), ['mappings']);
        $this->assertIsArray($template->getMappings());

        $this->assertEquals($template->getAliases(), ['aliases']);
        $this->assertIsArray($template->getAliases());

        $this->assertEquals($template->getMetadata(), ['metadata']);
        $this->assertIsArray($template->getMetadata());

        $this->assertEquals($template->getComposedOf(), ['composedof']);
        $this->assertIsArray($template->getComposedOf());

        $this->assertEquals($template->getDataStream(), true);
        $this->assertIsBool($template->getDataStream());

        $this->assertEquals($template->isSystem(), false);
        $this->assertIsBool($template->isSystem());

        $template->setName('.name');
        $this->assertEquals($template->isSystem(), true);
        $this->assertIsBool($template->isSystem());

        /*$this->assertEquals($template->getJson(), [
            'index_patterns' => ['index-patterns'],
            'version' => $template->getVersion(),
            'priority' => $template->getPriority(),
            'composed_of' => $template->getComposedOf(),
            'template' => [
                'settings' => $template->getSettings(),
                'mappings' => $template->getMappings(),
                'aliases' => $template->getAliases(),
            ],
            '_meta' => $template->getMetadata(),
            'data_stream' => (object)[],
        ]);*/
        $this->assertIsArray($template->getJson());
    }
}
