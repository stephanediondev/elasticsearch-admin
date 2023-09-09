<?php

namespace App\Tests\Manager;

use App\Manager\ElasticsearchIndexManager;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class ElasticsearchIndexManagerTest extends WebTestCase
{
    public function testGetByNameNull(): void
    {
        $elasticsearchIndexManager = static::getContainer()->get('App\Manager\ElasticsearchIndexManager');

        if ($elasticsearchIndexManager instanceof ElasticsearchIndexManager) {
            $index = $elasticsearchIndexManager->getByName(uniqid());

            $this->assertNull($index);
        }
    }
}
