<?php

namespace App\Tests\Manager;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class ElasticsearchComponentTemplateManagerTest extends WebTestCase
{
    public function testGetByNameNull(): void
    {
        $elasticsearchComponentTemplateManager = static::getContainer()->get('App\Manager\ElasticsearchComponentTemplateManager');

        $template = $elasticsearchComponentTemplateManager->getByName(uniqid());

        $this->assertNull($template);
    }
}
