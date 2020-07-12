<?php

namespace App\Tests\Manager;

use App\Model\ElasticsearchRepositoryModel;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class ElasticsearchRepositoryManagerTest extends WebTestCase
{
    public function testGetByName404()
    {
        self::bootKernel();
        $container = self::$kernel->getContainer();

        $elasticsearchRepositoryManager = self::$container->get('App\Manager\ElasticsearchRepositoryManager');

        $repository = $elasticsearchRepositoryManager->getByName(uniqid());

        $this->assertNull($repository);
    }
}
