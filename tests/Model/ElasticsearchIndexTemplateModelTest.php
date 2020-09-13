<?php

namespace App\Tests\Model;

use App\Model\ElasticsearchIndexTemplateModel;
use PHPUnit\Framework\TestCase;

class ElasticsearchIndexTemplateModelTest extends TestCase
{
    public function test()
    {
        $template = new ElasticsearchIndexTemplateModel();
        $template->setName('name');
        $template->setIndexPatterns('index-patterns');
        $template->setVersion(1);
        $template->setPriority(2);
        $template->setSettings('');
        $template->setSettings([]);
        $template->setSetting('setting-key', 'setting-value');
        $template->setMappings('');
        $template->setMappings(['mappings']);
        $template->setAliases('');
        $template->setAliases(['aliases']);
        $template->setMetadata('');
        $template->setMetadata(['metadata']);
        $template->setComposedOf(['composedof']);
        $template->setDataStream(true);

        $this->assertEquals($template->getName(), 'name');
        $this->assertEquals(strval($template), 'name');

        $this->assertEquals($template->getIndexPatterns(), 'index-patterns');

        $this->assertEquals($template->getVersion(), 1);
        $this->assertIsInt($template->getVersion());

        $this->assertEquals($template->getPriority(), 2);
        $this->assertIsInt($template->getPriority());

        $this->assertEquals($template->getSettings(), ['setting-key' => 'setting-value']);
        $this->assertIsArray($template->getSettings());
        $this->assertEquals($template->getSetting('setting-key'), 'setting-value');

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

        $template->setName('.name');
        $this->assertEquals($template->isSystem(), true);
    }
}
