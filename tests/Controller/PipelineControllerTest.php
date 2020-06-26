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
     * @Route("/pipelines/create", name="pipelines_create")
     */
    public function testCreate()
    {
        $this->client->request('GET', '/admin/pipelines/create');

        $this->assertResponseStatusCodeSame(200);
        $this->assertPageTitleSame('Pipelines - Create pipeline');
    }

    /**
     * @Route("/pipelines/{name}", name="pipelines_read")
     */
    public function testRead404()
    {
        $this->client->request('GET', '/admin/pipelines/'.uniqid());

        $this->assertResponseStatusCodeSame(404);
    }

    /**
     * @Route("/pipelines/{name}/update", name="pipelines_update")
     */
    public function testUpdate404()
    {
        $this->client->request('GET', '/admin/pipelines/'.uniqid().'/update');

        $this->assertResponseStatusCodeSame(404);
    }
}
