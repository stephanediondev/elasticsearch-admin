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

    public function testEditPersistent()
    {
        $this->client->request('GET', '/admin/cluster/settings/persistent/cluster.routing.allocation.disk.watermark.low/edit');

        $this->assertResponseStatusCodeSame(200);
    }

    public function testEditTransient()
    {
        $this->client->request('GET', '/admin/cluster/settings/transient/cluster.routing.allocation.disk.watermark.low/edit');

        $this->assertResponseStatusCodeSame(200);
    }
}
