<?php

namespace App\Tests\Controller;

class UserControllerTest extends AbstractAppControllerTest
{
    public function testIndex()
    {
        $this->client->request('GET', '/admin/users');

        $this->assertResponseStatusCodeSame(200);
        $this->assertPageTitleSame('Users');
    }

    public function testCreate()
    {
        $this->client->request('GET', '/admin/users/create');

        $this->assertResponseStatusCodeSame(200);
        $this->assertPageTitleSame('Users - Create user');
    }

    public function testRead404()
    {
        $this->client->request('GET', '/admin/user/'.uniqid());

        $this->assertResponseStatusCodeSame(404);
    }

    public function testUpdate404()
    {
        $this->client->request('GET', '/admin/user/'.uniqid().'/update');

        $this->assertResponseStatusCodeSame(404);
    }
}
