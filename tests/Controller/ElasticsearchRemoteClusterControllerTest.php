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

        if (false == $this->callManager->checkVersion('5.4.0')) {
            $this->assertResponseStatusCodeSame(403);
        } else {
            $this->assertResponseStatusCodeSame(200);
            $this->assertPageTitleSame('Remote clusters');
        }
    }
}
