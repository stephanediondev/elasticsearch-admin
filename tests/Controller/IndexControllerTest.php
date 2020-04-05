<?php

namespace App\Tests\Controller;

use App\Tests\Controller;

class IndexControllerTest extends AbstractAppControllerTest
{
    public function testIndex()
    {
        $this->client->request('GET', '/admin/indices');

        $this->assertResponseStatusCodeSame(200);
    }

    public function testReindex()
    {
        $this->client->request('GET', '/admin/indices/reindex');

        $this->assertResponseStatusCodeSame(200);
    }

    public function testCreate()
    {
        $this->client->request('GET', '/admin/indices/create');

        $this->assertResponseStatusCodeSame(200);
    }
}
