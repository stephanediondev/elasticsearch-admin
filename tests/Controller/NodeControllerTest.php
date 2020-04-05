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
}
