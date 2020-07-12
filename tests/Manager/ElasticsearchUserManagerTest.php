<?php

namespace App\Tests\Manager;

use App\Model\ElasticsearchUserModel;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class ElasticsearchUserManagerTest extends WebTestCase
{
    public function testGetByName404()
    {
        self::bootKernel();
        $container = self::$kernel->getContainer();

        $elasticsearchUserManager = self::$container->get('App\Manager\ElasticsearchUserManager');

        $user = $elasticsearchUserManager->getByName(uniqid());

        $this->assertNull($user);
    }
}
