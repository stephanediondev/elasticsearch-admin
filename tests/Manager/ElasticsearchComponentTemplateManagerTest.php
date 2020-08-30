<?php

namespace App\Tests\Manager;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class ElasticsearchComponentTemplateManagerTest extends WebTestCase
{
    public function testGetByNameNull()
    {
        self::bootKernel();
        $container = self::$kernel->getContainer();

        $elasticsearchComponentTemplateManager = self::$container->get('App\Manager\ElasticsearchComponentTemplateManager');

        $template = $elasticsearchComponentTemplateManager->getByName(uniqid());

        $this->assertNull($template);
    }
}
