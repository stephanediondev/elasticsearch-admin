<?php

namespace App\Tests\Manager;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class ElasticsearchIndexTemplateManagerTest extends WebTestCase
{
    public function testGetByNameNull(): void
    {
        self::bootKernel();
        $container = self::$kernel->getContainer();

        $elasticsearchIndexTemplateManager = static::getContainer()->get('App\Manager\ElasticsearchIndexTemplateManager');

        $template = $elasticsearchIndexTemplateManager->getByName(uniqid());

        $this->assertNull($template);
    }
}
