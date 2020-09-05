<?php

namespace App\Tests\Controller;

/**
 * @Route("/admin")
 */
class ElasticsearchDataStreamControllerTest extends AbstractAppControllerTest
{
    /**
     * @Route("/data-streams", name="data_streams")
     */
    public function testIndex()
    {
        $this->client->request('GET', '/admin/data-streams');

        if (false == $this->callManager->hasFeature('data_streams')) {
            $this->assertResponseStatusCodeSame(403);
        } else {
            $this->assertResponseStatusCodeSame(200);
            $this->assertPageTitleSame('Data streams');
        }
    }

    /**
     * @Route("/data-streams/{name}", name="data_streams_read")
     */
    public function testRead404()
    {
        $this->client->request('GET', '/admin/data-streams/'.uniqid());

        if (false == $this->callManager->hasFeature('data_streams')) {
            $this->assertResponseStatusCodeSame(403);
        } else {
            $this->assertResponseStatusCodeSame(404);
        }
    }

    /**
     * @Route("/data-streams/{name}/stats", name="data_streams_read_stats")
     */
    public function testStats404()
    {
        $this->client->request('GET', '/admin/data-streams/'.uniqid().'/stats');

        if (false == $this->callManager->hasFeature('data_streams')) {
            $this->assertResponseStatusCodeSame(403);
        } else {
            $this->assertResponseStatusCodeSame(404);
        }
    }

    /**
     * @Route("/data-streams/{name}/delete", name="data_streams_delete")
     */
    public function testDelete404()
    {
        $this->client->request('GET', '/admin/data-streams/'.uniqid().'/delete');

        if (false == $this->callManager->hasFeature('data_streams')) {
            $this->assertResponseStatusCodeSame(403);
        } else {
            $this->assertResponseStatusCodeSame(404);
        }
    }
}
