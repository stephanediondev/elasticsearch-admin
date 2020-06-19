<?php

namespace App\Tests\Controller;

use App\Model\CallRequestModel;

class ClusterControllerTest extends AbstractAppControllerTest
{
    public function testRead()
    {
        $callRequest = new CallRequestModel();
        $callRequest->setPath('/_cluster/health');
        $callResponse = $this->callManager->call($callRequest);
        $clusterHealth = $callResponse->getContent();

        $this->client->request('GET', '/admin/cluster');

        $this->assertResponseStatusCodeSame(200);
        $this->assertPageTitleSame('Cluster - '.$clusterHealth['cluster_name']);
    }

    public function testSettings()
    {
        $callRequest = new CallRequestModel();
        $callRequest->setPath('/_cluster/health');
        $callResponse = $this->callManager->call($callRequest);
        $clusterHealth = $callResponse->getContent();

        $this->client->request('GET', '/admin/cluster/settings');

        $this->assertResponseStatusCodeSame(200);
        $this->assertPageTitleSame('Cluster - '.$clusterHealth['cluster_name'].' - Settings');
    }

    public function testEditPersistent()
    {
        $this->client->request('GET', '/admin/cluster/settings/persistent/cluster.routing.allocation.disk.watermark.low/edit');

        $this->assertResponseStatusCodeSame(200);
        $this->assertPageTitleSame('Cluster - cluster.routing.allocation.disk.watermark.low');
    }

    public function testEditTransient()
    {
        $this->client->request('GET', '/admin/cluster/settings/transient/cluster.routing.allocation.disk.watermark.low/edit');

        $this->assertResponseStatusCodeSame(200);
        $this->assertPageTitleSame('Cluster - cluster.routing.allocation.disk.watermark.low');
    }
}
