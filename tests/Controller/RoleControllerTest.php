<?php

namespace App\Tests\Controller;

class RoleControllerTest extends AbstractAppControllerTest
{
    public function testIndex()
    {
        $this->client->request('GET', '/admin/roles');

        $this->assertResponseStatusCodeSame(200);
        $this->assertPageTitleSame('Roles');
    }

    public function testCreate()
    {
        $this->client->request('GET', '/admin/roles/create');

        $this->assertResponseStatusCodeSame(200);
        $this->assertPageTitleSame('Roles - Create role');
    }

    public function testRead404()
    {
        $this->client->request('GET', '/admin/role/'.uniqid());

        $this->assertResponseStatusCodeSame(404);
    }

    public function testUpdate404()
    {
        $this->client->request('GET', '/admin/role/'.uniqid().'/update');

        $this->assertResponseStatusCodeSame(404);
    }
}
