<?php

namespace App\Tests\Manager;

use App\Model\ElasticsearchComponentTemplateModel;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class ElasticsearchComponentTemplateManagerTest extends WebTestCase
{
    public function testGetByName404()
    {
        self::bootKernel();
        $container = self::$kernel->getContainer();

        $elasticsearchComponentTemplateManager = self::$container->get('App\Manager\ElasticsearchComponentTemplateManager');

        $template = $elasticsearchComponentTemplateManager->getByName(uniqid());

        $this->assertNull($template);
    }
}
