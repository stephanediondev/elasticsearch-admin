<?php

namespace App\Tests\Controller;

use App\Tests\Controller;

class IlmControllerTest extends AbstractAppControllerTest
{
    public function testIndex()
    {
        $this->client->request('GET', '/admin/ilm');

        $this->assertResponseStatusCodeSame(200);
    }

    public function testStatus()
    {
        $this->client->request('GET', '/admin/ilm/status');

        $this->assertResponseStatusCodeSame(200);
    }

    public function testRead404()
    {
        $this->client->request('GET', '/admin/ilm/'.uniqid());

        $this->assertResponseStatusCodeSame(404);
    }
}
