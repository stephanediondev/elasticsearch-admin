<?php

namespace App\Tests\Model;

use App\Model\AppUserModel;
use PHPUnit\Framework\TestCase;

class AppUserModelTest extends TestCase
{
    public function test(): void
    {
        $user = new AppUserModel();
        $user->setId('id');
        $user->setEmail('email');
        $user->setPassword('password');
        $user->setPasswordPlain('password-plain');
        $user->setChangePassword(true);
        $user->setSecretRegister('secret-register');
        $user->setRoles(['ROLE_TEST']);
        $user->setCreatedAt(new \Datetime());

        $this->assertEquals($user->getId(), 'id');
        $this->assertIsString($user->getId());

        $this->assertEquals($user->getEmail(), 'email');
        $this->assertEquals(strval($user), 'email');
        $this->assertIsString($user->getEmail());

        $this->assertEquals($user->getUserIdentifier(), 'email');
        $this->assertIsString($user->getUserIdentifier());

        $this->assertEquals($user->getPassword(), 'password');
        $this->assertIsString($user->getPassword());

        $this->assertEquals($user->getPasswordPlain(), 'password-plain');
        $this->assertIsString($user->getPasswordPlain());

        $this->assertEquals($user->getChangePassword(), true);
        $this->assertIsBool($user->getChangePassword());

        $this->assertEquals($user->getSecretRegister(), 'secret-register');
        $this->assertIsString($user->getSecretRegister());

        $this->assertEquals($user->getRoles(), ['ROLE_TEST', 'ROLE_USER']);
        $this->assertIsArray($user->getRoles());

        $this->assertInstanceOf('Datetime', $user->getCreatedAt());

        $this->assertEquals($user->getJson(), [
            'email' => $user->getEmail(),
            'password' => $user->getPassword(),
            'roles' => $user->getRoles(),
            'created_at' => $user->getCreatedAt()->format('Y-m-d H:i:s'),
        ]);
        $this->assertIsArray($user->getJson());
    }
}
