<?php

namespace App\Tests\Manager;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class ElasticsearchIndexTemplateLegacyManagerTest extends WebTestCase
{
    public function testGetByNameNull()
    {
        self::bootKernel();
        $container = self::$kernel->getContainer();

        $elasticsearchIndexTemplateLegacyManager = self::$container->get('App\Manager\ElasticsearchIndexTemplateLegacyManager');

        $template = $elasticsearchIndexTemplateLegacyManager->getByName(uniqid());

        $this->assertNull($template);
    }
}
