<?php

namespace App\Tests\Manager;

use App\Model\ElasticsearchSnapshotModel;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class ElasticsearchSnapshotManagerTest extends WebTestCase
{
    public function testGetByNameAndRepository404()
    {
        self::bootKernel();
        $container = self::$kernel->getContainer();

        $elasticsearchSnapshotManager = self::$container->get('App\Manager\ElasticsearchSnapshotManager');

        $snapshot = $elasticsearchSnapshotManager->getByNameAndRepository(uniqid(), uniqid());

        $this->assertNull($snapshot);
    }
}
