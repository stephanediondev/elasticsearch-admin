<?php

namespace App\Tests\Manager;

use App\Manager\ElasticsearchRepositoryManager;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class ElasticsearchRepositoryManagerTest extends WebTestCase
{
    public function testGetByNameNull(): void
    {
        $elasticsearchRepositoryManager = static::getContainer()->get('App\Manager\ElasticsearchRepositoryManager');

        if ($elasticsearchRepositoryManager instanceof ElasticsearchRepositoryManager) {
            $repository = $elasticsearchRepositoryManager->getByName(uniqid());

            $this->assertNull($repository);
        }
    }
}
