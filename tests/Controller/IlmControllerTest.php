<?php

namespace App\Tests\Controller;

/**
 * @Route("/admin")
 */
class IlmControllerTest extends AbstractAppControllerTest
{
    /**
     * @Route("/ilm", name="ilm")
     */
    public function testIndex()
    {
        $this->client->request('GET', '/admin/ilm');

        $this->assertResponseStatusCodeSame(200);
        $this->assertPageTitleSame('ILM policies');
    }

    /**
     * @Route("/ilm/status", name="ilm_status")
     */
    public function testStatus()
    {
        $this->client->request('GET', '/admin/ilm/status');

        $this->assertResponseStatusCodeSame(200);
        $this->assertPageTitleSame('ILM policies - Status');
    }

    /**
     * @Route("/ilm/create", name="ilm_create")
     */
    public function testCreate()
    {
        $this->client->request('GET', '/admin/ilm/create');

        $this->assertResponseStatusCodeSame(200);
        $this->assertPageTitleSame('ILM policies - Create ILM policy');
    }

    /**
     * @Route("/ilm/{name}", name="ilm_read")
     */
    public function testRead404()
    {
        $this->client->request('GET', '/admin/ilm/'.uniqid());

        $this->assertResponseStatusCodeSame(404);
    }
}
