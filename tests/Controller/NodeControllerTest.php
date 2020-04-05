<?php

namespace App\Tests\Controller;

use App\Tests\Controller;

class NodeControllerTest extends AbstractAppControllerTest
{
    public function testIndex()
    {
        $this->client->request('GET', '/admin/nodes');

        $this->assertResponseStatusCodeSame(200);
    }

    public function testFetch()
    {
        $this->client->request('GET', '/admin/nodes/fetch');

        $this->assertResponseStatusCodeSame(200);
        $this->assertTrue($this->client->getResponse()->headers->contains('Content-Type', 'application/json'));
    }
}
