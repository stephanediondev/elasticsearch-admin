<?php

namespace App\Tests\Manager;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class ElasticsearchUserManagerTest extends WebTestCase
{
    public function testGetByNameNull()
    {
        self::bootKernel();
        $container = self::$kernel->getContainer();

        $elasticsearchUserManager = static::getContainer()->get('App\Manager\ElasticsearchUserManager');

        $user = $elasticsearchUserManager->getByName(uniqid());

        $this->assertNull($user);
    }
}
