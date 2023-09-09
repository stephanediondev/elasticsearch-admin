<?php

namespace App\Tests\Manager;

use App\Manager\ElasticsearchPipelineManager;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class ElasticsearchPipelineManagerTest extends WebTestCase
{
    public function testGetByNameNull(): void
    {
        $elasticsearchPipelineManager = static::getContainer()->get('App\Manager\ElasticsearchPipelineManager');

        if ($elasticsearchPipelineManager instanceof ElasticsearchPipelineManager) {
            $pipeline = $elasticsearchPipelineManager->getByName(uniqid());

            $this->assertNull($pipeline);
        }
    }
}
