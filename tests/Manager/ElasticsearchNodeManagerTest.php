<?php

namespace App\Tests\Manager;

use App\Manager\ElasticsearchNodeManager;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class ElasticsearchNodeManagerTest extends WebTestCase
{
    public function testGetByNameNull(): void
    {
        /**
         * @var ElasticsearchNodeManager $elasticsearchNodeManager
         */
        $elasticsearchNodeManager = static::getContainer()->get('App\Manager\ElasticsearchNodeManager');

        $node = $elasticsearchNodeManager->getByName(uniqid());

        $this->assertNull($node);
    }
}
