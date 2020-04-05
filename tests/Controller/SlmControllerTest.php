<?php

namespace App\Tests\Controller;

use App\Tests\Controller;

class SlmControllerTest extends AbstractAppControllerTest
{
    public function testIndex()
    {
        $this->client->request('GET', '/admin/slm');

        $this->assertResponseStatusCodeSame(200);
    }

    public function testStatus()
    {
        $this->client->request('GET', '/admin/slm/status');

        $this->assertResponseStatusCodeSame(200);
    }

    public function testStats()
    {
        $this->client->request('GET', '/admin/slm/stats');

        $this->assertResponseStatusCodeSame(200);
    }

    public function testCreate()
    {
        $this->client->request('GET', '/admin/slm/create');

        $this->assertResponseStatusCodeSame(200);
    }
}
