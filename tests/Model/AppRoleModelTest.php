<?php

namespace App\Tests\Model;

use App\Model\AppRoleModel;
use PHPUnit\Framework\TestCase;

class AppRoleModelTest extends TestCase
{
    public function test()
    {
        $role = new AppRoleModel();
        $role->setId('id');
        $role->setName('name');
        $role->setCreatedAt(new \Datetime());

        $this->assertEquals($role->getId(), 'id');

        $this->assertEquals($role->getName(), 'name');
        $this->assertEquals(strval($role), 'name');

        $this->assertInstanceOf('Datetime', $role->getCreatedAt());
    }
}
