<?php

namespace App\Tests\Model;

use App\Model\AppUserModel;
use PHPUnit\Framework\TestCase;

class AppUserModelTest extends TestCase
{
    public function test()
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

        $this->assertEquals($user->getEmail(), 'email');
        $this->assertEquals(strval($user), 'email');
        $this->assertEquals($user->getUsername(), 'email');

        $this->assertEquals($user->getPassword(), 'password');
        $this->assertEquals($user->getPasswordPlain(), 'password-plain');

        $this->assertEquals($user->getChangePassword(), true);
        $this->assertIsBool($user->getChangePassword());

        $this->assertEquals($user->getSecretRegister(), 'secret-register');

        $this->assertEquals($user->getRoles(), ['ROLE_TEST', 'ROLE_USER']);
        $this->assertIsArray($user->getRoles());

        $this->assertInstanceOf('Datetime', $user->getCreatedAt());
    }
}
