<?php

namespace App\Tests\Manager;

use App\Model\ElasticsearchIndexModel;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class ElasticsearchIndexManagerTest extends WebTestCase
{
    public function testGetByName404()
    {
        self::bootKernel();
        $container = self::$kernel->getContainer();

        $elasticsearchIndexManager = self::$container->get('App\Manager\ElasticsearchIndexManager');

        $index = $elasticsearchIndexManager->getByName(uniqid());

        $this->assertNull($index);
    }
}
