<?php

namespace App\Tests\Model;

use App\Model\AppRoleModel;
use PHPUnit\Framework\TestCase;

class AppRoleModelTest extends TestCase
{
    public function test(): void
    {
        $role = new AppRoleModel();
        $role->setId('id');
        $role->setName('name');
        $role->setCreatedAt(new \Datetime());

        $this->assertEquals($role->getId(), 'id');
        $this->assertIsString($role->getId());

        $this->assertEquals($role->getName(), 'name');
        $this->assertEquals(strval($role), 'name');
        $this->assertIsString($role->getName());

        $this->assertInstanceOf('Datetime', $role->getCreatedAt());
    }
}
