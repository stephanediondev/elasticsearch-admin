<?php

namespace App\Tests\Controller;

class ClusterControllerTest extends AbstractAppControllerTest
{
    public function testRead()
    {
        $this->client->request('GET', '/admin/cluster');

        $this->assertResponseStatusCodeSame(200);
    }

    public function testSettings()
    {
        $this->client->request('GET', '/admin/cluster/settings');

        $this->assertResponseStatusCodeSame(200);
    }
}
