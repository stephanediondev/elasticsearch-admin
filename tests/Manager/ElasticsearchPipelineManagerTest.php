<?php

namespace App\Tests\Manager;

use App\Model\ElasticsearchPipelineModel;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class ElasticsearchPipelineManagerTest extends WebTestCase
{
    public function testGetByName404()
    {
        self::bootKernel();
        $container = self::$kernel->getContainer();

        $elasticsearchPipelineManager = self::$container->get('App\Manager\ElasticsearchPipelineManager');

        $pipeline = $elasticsearchPipelineManager->getByName(uniqid());

        $this->assertNull($pipeline);
    }
}
