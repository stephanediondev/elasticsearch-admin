<?php

namespace App\Tests\Model;

use App\Model\ElasticsearchIndexTemplateLegacyModel;
use PHPUnit\Framework\TestCase;

class ElasticsearchIndexTemplateLegacyModelTest extends TestCase
{
    public function test()
    {
        $template = new ElasticsearchIndexTemplateLegacyModel();
        $template->setName('name');
        $template->setIndexPatterns('index-patterns');
        $template->setTemplate('template');
        $template->setVersion(1);
        $template->setOrder(2);
        $template->setSettings('');
        $template->setSettings([]);
        $template->setSetting('setting-key', 'setting-value');
        $template->setMappings('');
        $template->setMappings(['mappings']);
        $template->setAliases('');
        $template->setAliases(['aliases']);

        $this->assertEquals($template->getName(), 'name');
        $this->assertEquals(strval($template), 'name');
        $this->assertIsString($template->getName());

        $this->assertEquals($template->getIndexPatterns(), 'index-patterns');
        $this->assertIsString($template->getIndexPatterns());

        $this->assertEquals($template->getTemplate(), 'template');
        $this->assertIsString($template->getTemplate());

        $this->assertEquals($template->getVersion(), 1);
        $this->assertIsInt($template->getVersion());

        $this->assertEquals($template->getOrder(), 2);
        $this->assertIsInt($template->getOrder());

        $this->assertEquals($template->getSettings(), ['setting-key' => 'setting-value']);
        $this->assertIsArray($template->getSettings());

        $this->assertEquals($template->getSetting('setting-key'), 'setting-value');
        $this->assertIsString($template->getSetting('setting-key'));

        $this->assertEquals($template->getMappings(), ['mappings']);
        $this->assertIsArray($template->getMappings());

        $this->assertEquals($template->getAliases(), ['aliases']);
        $this->assertIsArray($template->getAliases());

        $this->assertEquals($template->isSystem(), false);
        $this->assertIsBool($template->isSystem());

        $template->setName('.name');
        $this->assertEquals($template->isSystem(), true);
        $this->assertIsBool($template->isSystem());
    }
}
