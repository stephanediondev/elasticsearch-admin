<?php

namespace App\Tests\Manager;

use App\Manager\AppRoleManager;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class AppRoleManagerTest extends WebTestCase
{
    public function testGetByNameNull(): void
    {
        $appRoleManager = static::getContainer()->get('App\Manager\AppRoleManager');

        if ($appRoleManager instanceof AppRoleManager) {
            $role = $appRoleManager->getByName(uniqid());

            $this->assertNull($role);
        }
    }
}
