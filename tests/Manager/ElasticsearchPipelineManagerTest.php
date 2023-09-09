<?php

namespace App\Tests\Manager;

use App\Manager\ElasticsearchPipelineManager;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class ElasticsearchPipelineManagerTest extends WebTestCase
{
    public function testGetByNameNull(): void
    {
        /**
         * @var ElasticsearchPipelineManager $elasticsearchPipelineManager
         */
        $elasticsearchPipelineManager = static::getContainer()->get('App\Manager\ElasticsearchPipelineManager');

        $pipeline = $elasticsearchPipelineManager->getByName(uniqid());

        $this->assertNull($pipeline);
    }
}
