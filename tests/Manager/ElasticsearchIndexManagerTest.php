<?php

namespace App\Tests\Manager;

use App\Manager\ElasticsearchIndexManager;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class ElasticsearchIndexManagerTest extends WebTestCase
{
    public function testGetByNameNull(): void
    {
        /**
         * @var ElasticsearchIndexManager $elasticsearchIndexManager
         */
        $elasticsearchIndexManager = static::getContainer()->get('App\Manager\ElasticsearchIndexManager');

        $index = $elasticsearchIndexManager->getByName(uniqid());

        $this->assertNull($index);
    }
}
