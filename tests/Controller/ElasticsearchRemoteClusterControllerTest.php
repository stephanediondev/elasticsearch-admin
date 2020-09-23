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

        if (false == $this->callManager->hasFeature('remote_clusters')) {
            $this->assertResponseStatusCodeSame(403);
        } else {
            $this->assertResponseStatusCodeSame(200);
            $this->assertPageTitleSame('Remote clusters');
            $this->assertSelectorTextSame('h1', 'Remote clusters');
            $this->assertSelectorTextContains('h3', 'List');
        }
    }
}
