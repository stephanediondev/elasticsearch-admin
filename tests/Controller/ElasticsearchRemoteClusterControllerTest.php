<?php

namespace App\Tests\Controller;

/**
 * @Route("/admin")
 */
class ElasticsearchRemoteClusterControllerTest extends AbstractAppControllerTest
{
    /**
     * @Route("/remote-clusters", name="remote_clusters")
     */
    public function testIndex()
    {
        $this->client->request('GET', '/admin/remote-clusters');

        $this->assertResponseStatusCodeSame(200);
        $this->assertPageTitleSame('Remote clusters');
    }
}
