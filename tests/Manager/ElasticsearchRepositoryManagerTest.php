<?php

namespace App\Tests\Manager;

use App\Manager\ElasticsearchRepositoryManager;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class ElasticsearchRepositoryManagerTest extends WebTestCase
{
    public function testGetByNameNull(): void
    {
        /**
         * @var ElasticsearchRepositoryManager $elasticsearchRepositoryManager
         */
        $elasticsearchRepositoryManager = static::getContainer()->get('App\Manager\ElasticsearchRepositoryManager');

        $repository = $elasticsearchRepositoryManager->getByName(uniqid());

        $this->assertNull($repository);
    }
}
