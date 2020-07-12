<?php

namespace App\Tests\Manager;

use App\Model\ElasticsearchRoleModel;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class ElasticsearchRoleManagerTest extends WebTestCase
{
    public function testGetByName404()
    {
        self::bootKernel();
        $container = self::$kernel->getContainer();

        $elasticsearchRoleManager = self::$container->get('App\Manager\ElasticsearchRoleManager');

        $role = $elasticsearchRoleManager->getByName(uniqid());

        $this->assertNull($role);
    }
}
