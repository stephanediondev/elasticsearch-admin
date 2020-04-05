<?php

namespace App\Tests\Controller;

use App\Tests\Controller;

class SnapshotControllerTest extends AbstractAppControllerTest
{
    public function testIndex()
    {
        $this->client->request('GET', '/admin/snapshots');

        $this->assertResponseStatusCodeSame(200);
    }

    public function testCreate()
    {
        $this->client->request('GET', '/admin/snapshots/create');

        $this->assertResponseStatusCodeSame(200);
    }
}
