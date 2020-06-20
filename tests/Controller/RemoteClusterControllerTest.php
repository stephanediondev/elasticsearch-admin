<?php

namespace App\Tests\Controller;

class RemoteClusterControllerTest extends AbstractAppControllerTest
{
    public function testIndex()
    {
        $this->client->request('GET', '/admin/remote-clusters');

        $this->assertResponseStatusCodeSame(200);
        $this->assertPageTitleSame('Remote clusters');
    }
}
