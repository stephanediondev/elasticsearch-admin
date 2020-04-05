<?php

namespace App\Tests\Controller;

use App\Tests\Controller;

class UserControllerTest extends AbstractAppControllerTest
{
    public function testIndex()
    {
        $this->client->request('GET', '/admin/users');

        $this->assertResponseStatusCodeSame(200);
    }

    public function testCreate()
    {
        $this->client->request('GET', '/admin/users/create');

        $this->assertResponseStatusCodeSame(200);
    }
}
