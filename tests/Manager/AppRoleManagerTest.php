<?php

namespace App\Tests\Manager;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class AppRoleManagerTest extends WebTestCase
{
    public function testGetByNameNull(): void
    {
        $appRoleManager = static::getContainer()->get('App\Manager\AppRoleManager');

        $role = $appRoleManager->getByName(uniqid());

        $this->assertNull($role);
    }
}
