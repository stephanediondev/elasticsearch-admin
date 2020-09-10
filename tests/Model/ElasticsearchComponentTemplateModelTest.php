<?php

namespace App\Tests\Model;

use App\Model\ElasticsearchComponentTemplateModel;
use PHPUnit\Framework\TestCase;

class ElasticsearchComponentTemplateModelTest extends TestCase
{
    public function test()
    {
        $template = new ElasticsearchComponentTemplateModel();
        $template->setName('name');
        $template->setVersion(1);
        $template->setSettings('');
        $template->setSettings([]);
        $template->setSetting('setting-key', 'setting-value');
        $template->setMappings('');
        $template->setMappings([]);
        $template->setAliases('');
        $template->setAliases([]);
        $template->setMetadata('');
        $template->setMetadata([]);

        $this->assertEquals($template->getName(), 'name');

        $this->assertEquals($template->getVersion(), 1);
        $this->assertIsInt($template->getVersion());

        $this->assertEquals($template->getSettings(), ['setting-key' => 'setting-value']);
        $this->assertIsArray($template->getSettings());
        $this->assertEquals($template->getSetting('setting-key'), 'setting-value');

        $this->assertEquals($template->getMappings(), []);
        $this->assertIsArray($template->getMappings());

        $this->assertEquals($template->getAliases(), []);
        $this->assertIsArray($template->getAliases());

        $this->assertEquals($template->getMetadata(), []);
        $this->assertIsArray($template->getMetadata());

        $this->assertEquals($template->isSystem(), false);

        $template->setName('.name');
        $this->assertEquals($template->isSystem(), true);
    }
}
