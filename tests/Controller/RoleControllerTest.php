<?php

namespace App\Tests\Controller;

use App\Tests\Controller;

class RoleControllerTest extends AbstractAppControllerTest
{
    public function testIndex()
    {
        $this->client->request('GET', '/admin/roles');

        $this->assertResponseStatusCodeSame(200);
    }
}
