<?php

namespace App\Tests\Manager;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class AppUserManagerTest extends WebTestCase
{
    public function testGetByIdNull(): void
    {
        self::bootKernel();
        $container = self::$kernel->getContainer();

        $appUserManager = static::getContainer()->get('App\Manager\AppUserManager');

        $user = $appUserManager->getById(uniqid());

        $this->assertNull($user);
    }

    public function testGetByEmailNull(): void
    {
        self::bootKernel();
        $container = self::$kernel->getContainer();

        $appUserManager = static::getContainer()->get('App\Manager\AppUserManager');

        $user = $appUserManager->getByEmail(uniqid());

        $this->assertNull($user);
    }
}
