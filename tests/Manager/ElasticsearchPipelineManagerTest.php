<?php

namespace App\Tests\Manager;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class ElasticsearchPipelineManagerTest extends WebTestCase
{
    public function testGetByNameNull(): void
    {
        $elasticsearchPipelineManager = static::getContainer()->get('App\Manager\ElasticsearchPipelineManager');

        $pipeline = $elasticsearchPipelineManager->getByName(uniqid());

        $this->assertNull($pipeline);
    }
}
