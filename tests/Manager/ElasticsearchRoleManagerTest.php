<?php

namespace App\Tests\Manager;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class ElasticsearchRoleManagerTest extends WebTestCase
{
    public function testGetByNameNull(): void
    {
        self::bootKernel();
        $container = self::$kernel->getContainer();

        $elasticsearchRoleManager = static::getContainer()->get('App\Manager\ElasticsearchRoleManager');

        $role = $elasticsearchRoleManager->getByName(uniqid());

        $this->assertNull($role);
    }
}
