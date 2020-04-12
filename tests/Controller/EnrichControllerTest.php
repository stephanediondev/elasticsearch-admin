<?php

namespace App\Tests\Controller;

use App\Tests\Controller;

class EnrichControllerTest extends AbstractAppControllerTest
{
    public function testIndex()
    {
        $this->client->request('GET', '/admin/enrich');

        $this->assertResponseStatusCodeSame(200);
    }

    public function testStats()
    {
        $this->client->request('GET', '/admin/enrich/stats');

        $this->assertResponseStatusCodeSame(200);
    }

    public function testCreate()
    {
        $this->client->request('GET', '/admin/enrich/create');

        $this->assertResponseStatusCodeSame(200);
    }

    public function testRead404()
    {
        $this->client->request('GET', '/_enrich/policy/'.uniqid());

        $this->assertResponseStatusCodeSame(404);
    }
}
