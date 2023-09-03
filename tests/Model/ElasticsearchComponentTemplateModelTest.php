<?php

namespace App\Tests\Model;

use App\Model\ElasticsearchComponentTemplateModel;
use PHPUnit\Framework\TestCase;

class ElasticsearchComponentTemplateModelTest extends TestCase
{
    public function test(): void
    {
        $template = new ElasticsearchComponentTemplateModel();
        $template->setName('name');
        $template->setVersion(1);
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

        $this->assertEquals($template->getName(), 'name');
        $this->assertEquals(strval($template), 'name');
        $this->assertIsString($template->getName());

        $this->assertEquals($template->getVersion(), 1);
        $this->assertIsInt($template->getVersion());

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

        $this->assertEquals($template->isSystem(), false);
        $this->assertIsBool($template->isSystem());

        $template->setName('.name');
        $this->assertEquals($template->isSystem(), true);
        $this->assertIsBool($template->isSystem());

        /*$this->assertEquals($template->getJson(), [
            'template' => [
                'settings' => $template->getSettings(),
                'mappings' => $template->getMappings(),
                'aliases' => $template->getAliases(),
            ],
            'version' => $template->getVersion(),
            '_meta' => $template->getMetadata(),
        ]);*/
        $this->assertIsArray($template->getJson());
    }
}
