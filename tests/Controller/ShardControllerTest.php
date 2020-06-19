<?php

namespace App\Tests\Controller;

class ShardControllerTest extends AbstractAppControllerTest
{
    public function testIndex()
    {
        $this->client->request('GET', '/admin/shards');

        $this->assertResponseStatusCodeSame(200);
        $this->assertPageTitleSame('Shards');
    }
}
