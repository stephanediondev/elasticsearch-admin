<?php

namespace App\Tests\Manager;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class ElasticsearchIndexManagerTest extends WebTestCase
{
    public function testGetByNameNull()
    {
        self::bootKernel();
        $container = self::$kernel->getContainer();

        $elasticsearchIndexManager = static::getContainer()->get('App\Manager\ElasticsearchIndexManager');

        $index = $elasticsearchIndexManager->getByName(uniqid());

        $this->assertNull($index);
    }
}
