<?php

namespace App\Tests\Controller;

/**
 * @Route("/admin")
 */
class PipelineControllerTest extends AbstractAppControllerTest
{
    /**
     * @Route("/pipelines", name="pipelines")
     */
    public function testIndex()
    {
        $this->client->request('GET', '/admin/pipelines');

        $this->assertResponseStatusCodeSame(200);
        $this->assertPageTitleSame('Pipelines');
    }

    /**
     * @Route("/pipelines/{name}", name="pipelines_read")
     */
    public function testRead404()
    {
        $this->client->request('GET', '/_ingest/pipeline/'.uniqid());

        $this->assertResponseStatusCodeSame(404);
    }
}
