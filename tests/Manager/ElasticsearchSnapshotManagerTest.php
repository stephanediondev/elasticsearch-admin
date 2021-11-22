<?php

namespace App\Tests\Manager;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class ElasticsearchSnapshotManagerTest extends WebTestCase
{
    public function testGetByNameAndRepositoryNull(): void
    {
        $elasticsearchSnapshotManager = static::getContainer()->get('App\Manager\ElasticsearchSnapshotManager');

        $snapshot = $elasticsearchSnapshotManager->getByNameAndRepository(uniqid(), uniqid());

        $this->assertNull($snapshot);
    }
}
