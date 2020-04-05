<?php

namespace App\Tests\Controller;

use App\Tests\Controller;

class ShardControllerTest extends AbstractAppControllerTest
{
    public function testIndex()
    {
        $this->client->request('GET', '/admin/shards');

        $this->assertResponseStatusCodeSame(200);
    }
}
