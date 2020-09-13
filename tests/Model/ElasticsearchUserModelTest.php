<?php

namespace App\Tests\Model;

use App\Model\ElasticsearchUserModel;
use PHPUnit\Framework\TestCase;

class ElasticsearchUserModelTest extends TestCase
{
    public function test()
    {
        $user = new ElasticsearchUserModel();
        $user->setName('name');
        $user->setEmail('email');
        $user->setPassword('password');
        $user->setFullname('fullname');
        $user->setEnabled(true);
        $user->setChangePassword(true);
        $user->setRoles(['roles']);

        $this->assertEquals($user->getName(), 'name');
        $this->assertEquals(strval($user), 'name');

        $this->assertEquals($user->getEmail(), 'email');
        $this->assertEquals($user->getPassword(), 'password');
        $this->assertEquals($user->getFullname(), 'fullname');

        $this->assertEquals($user->getChangePassword(), true);
        $this->assertIsBool($user->getChangePassword());

        $this->assertEquals($user->getEnabled(), true);
        $this->assertIsBool($user->getEnabled());

        $this->assertEquals($user->getRoles(), ['roles']);
        $this->assertIsArray($user->getRoles());
    }
}
