<?php

namespace App\Tests\Controller;

use App\Tests\Controller\AbstractAppControllerTest;

class ElasticsearchClusterControllerTest extends AbstractAppControllerTest
{
    public function testRead(): void
    {
        $clusterHealth = $this->elasticsearchClusterManager->getClusterHealth();

        $this->client->request('GET', '/admin/cluster');

        $this->assertResponseStatusCodeSame(200);
        $this->assertPageTitleSame('Cluster - '.$clusterHealth['cluster_name']);
        $this->assertSelectorTextSame('h1', 'Cluster');
        $this->assertSelectorTextSame('h2', $clusterHealth['cluster_name'].' '.ucfirst($clusterHealth['status']));
        $this->assertSelectorTextSame('h3', 'Summary');
    }

    public function testAllocationExplain(): void
    {
        $clusterHealth = $this->elasticsearchClusterManager->getClusterHealth();

        $this->client->request('GET', '/admin/cluster/allocation/explain');

        if (false == $this->callManager->hasFeature('allocation_explain')) {
            $this->assertResponseStatusCodeSame(403);
        } else {
            $this->assertResponseStatusCodeSame(200);
            $this->assertPageTitleSame('Cluster - '.$clusterHealth['cluster_name'].' - Allocation explain');
            $this->assertSelectorTextSame('h1', 'Cluster');
            $this->assertSelectorTextSame('h2', $clusterHealth['cluster_name'].' '.ucfirst($clusterHealth['status']));
            $this->assertSelectorTextSame('h3', 'Allocation explain');
        }
    }

    public function testSettings(): void
    {
        $clusterHealth = $this->elasticsearchClusterManager->getClusterHealth();

        $this->client->request('GET', '/admin/cluster/settings');

        if (false == $this->callManager->hasFeature('cluster_settings')) {
            $this->assertResponseStatusCodeSame(403);
        } else {
            $this->assertResponseStatusCodeSame(200);
            $this->assertPageTitleSame('Cluster - '.$clusterHealth['cluster_name'].' - Settings');
            $this->assertSelectorTextSame('h1', 'Cluster');
            $this->assertSelectorTextSame('h2', $clusterHealth['cluster_name'].' '.ucfirst($clusterHealth['status']));
            $this->assertSelectorTextSame('h3', 'Settings');
        }
    }

    public function testEditPersistent(): void
    {
        $clusterHealth = $this->elasticsearchClusterManager->getClusterHealth();

        $this->client->request('GET', '/admin/cluster/settings/persistent/cluster.routing.allocation.disk.watermark.low/edit');

        if (false == $this->callManager->hasFeature('cluster_settings')) {
            $this->assertResponseStatusCodeSame(403);
        } else {
            $this->assertResponseStatusCodeSame(200);
            $this->assertPageTitleSame('Cluster - '.$clusterHealth['cluster_name'].' - cluster.routing.allocation.disk.watermark.low');
            $this->assertSelectorTextSame('h1', 'Cluster');
            $this->assertSelectorTextSame('h2', $clusterHealth['cluster_name'].' '.ucfirst($clusterHealth['status']));
            $this->assertSelectorTextSame('h3', 'Edit as persistent');
        }
    }

    public function testEditTransient(): void
    {
        $clusterHealth = $this->elasticsearchClusterManager->getClusterHealth();

        $this->client->request('GET', '/admin/cluster/settings/transient/cluster.routing.allocation.disk.watermark.low/edit');

        if (false == $this->callManager->hasFeature('cluster_settings')) {
            $this->assertResponseStatusCodeSame(403);
        } else {
            $this->assertResponseStatusCodeSame(200);
            $this->assertPageTitleSame('Cluster - '.$clusterHealth['cluster_name'].' - cluster.routing.allocation.disk.watermark.low');
            $this->assertSelectorTextSame('h1', 'Cluster');
            $this->assertSelectorTextSame('h2', $clusterHealth['cluster_name'].' '.ucfirst($clusterHealth['status']));
            $this->assertSelectorTextSame('h3', 'Edit as transient');
        }
    }

    public function testAudit(): void
    {
        $clusterHealth = $this->elasticsearchClusterManager->getClusterHealth();

        $this->client->request('GET', '/admin/cluster/audit');

        $this->assertResponseStatusCodeSame(200);
        $this->assertPageTitleSame('Cluster - '.$clusterHealth['cluster_name'].' - Audit');
        $this->assertSelectorTextSame('h1', 'Cluster');
        $this->assertSelectorTextSame('h2', $clusterHealth['cluster_name'].' '.ucfirst($clusterHealth['status']));
        $this->assertSelectorTextSame('h3', 'Audit');
    }

    public function testDiskThresholds(): void
    {
        $clusterHealth = $this->elasticsearchClusterManager->getClusterHealth();

        $this->client->request('GET', '/admin/cluster/disk-thresholds');

        if (false == $this->callManager->hasFeature('cluster_settings')) {
            $this->assertResponseStatusCodeSame(403);
        } else {
            $this->assertResponseStatusCodeSame(200);
            $this->assertPageTitleSame('Cluster - '.$clusterHealth['cluster_name'].' - Disk thresholds');
            $this->assertSelectorTextSame('h1', 'Cluster');
            $this->assertSelectorTextSame('h2', $clusterHealth['cluster_name'].' '.ucfirst($clusterHealth['status']));
            $this->assertSelectorTextSame('h3', 'Disk thresholds');
        }
    }
}
