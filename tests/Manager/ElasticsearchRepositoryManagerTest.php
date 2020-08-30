<?php

namespace App\Tests\Manager;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class ElasticsearchRepositoryManagerTest extends WebTestCase
{
    public function testGetByNameNull()
    {
        self::bootKernel();
        $container = self::$kernel->getContainer();

        $elasticsearchRepositoryManager = self::$container->get('App\Manager\ElasticsearchRepositoryManager');

        $repository = $elasticsearchRepositoryManager->getByName(uniqid());

        $this->assertNull($repository);
    }
}
