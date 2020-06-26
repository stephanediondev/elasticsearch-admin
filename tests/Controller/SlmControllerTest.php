<?php

namespace App\Tests\Controller;

/**
 * @Route("/admin")
 */
class SlmControllerTest extends AbstractAppControllerTest
{
    /**
     * @Route("/slm", name="slm")
     */
    public function testIndex()
    {
        $this->client->request('GET', '/admin/slm');

        if (true == isset($this->xpack['features']['slm']) && true == $this->xpack['features']['slm']['enabled']) {
            $this->assertResponseStatusCodeSame(200);
            $this->assertPageTitleSame('SLM policies');
        } else {
            $this->assertResponseStatusCodeSame(500);
        }
    }

    /**
     * @Route("/slm/stats", name="slm_stats")
     */
    public function testStats()
    {
        $this->client->request('GET', '/admin/slm/stats');

        if (true == isset($this->xpack['features']['slm']) && true == $this->xpack['features']['slm']['enabled']) {
            $this->assertResponseStatusCodeSame(200);
            $this->assertPageTitleSame('SLM policies - Stats');
        } else {
            $this->assertResponseStatusCodeSame(500);
        }
    }

    /**
     * @Route("/slm/status", name="slm_status")
     */
    public function testStatus()
    {
        $this->client->request('GET', '/admin/slm/status');

        if (true == isset($this->xpack['features']['slm']) && true == $this->xpack['features']['slm']['enabled']) {
            $this->assertResponseStatusCodeSame(200);
            $this->assertPageTitleSame('SLM policies - Status');
        } else {
            $this->assertResponseStatusCodeSame(500);
        }
    }

    /**
     * @Route("/slm/create", name="slm_create")
     */
    public function testCreate()
    {
        $this->client->request('GET', '/admin/slm/create');

        $this->assertResponseStatusCodeSame(200);
        $this->assertPageTitleSame('SLM policies - Create SLM policy');
    }

    /**
     * @Route("/slm/{name}", name="slm_read")
     */
    public function testRead404()
    {
        $this->client->request('GET', '/admin/slm/'.uniqid());

        $this->assertResponseStatusCodeSame(404);
    }

    /**
     * @Route("/slm/{name}/history", name="slm_read_history")
     */
    public function testReadHistory404()
    {
        $this->client->request('GET', '/admin/slm/'.uniqid().'/history');

        $this->assertResponseStatusCodeSame(404);
    }

    /**
     * @Route("/slm/{name}/stats", name="slm_read_stats")
     */
    public function testReadStats404()
    {
        $this->client->request('GET', '/admin/slm/'.uniqid().'/stats');

        $this->assertResponseStatusCodeSame(404);
    }

    /**
     * @Route("/slm/{name}/update", name="slm_update")
     */
    public function testUpdate404()
    {
        $this->client->request('GET', '/admin/slm/'.uniqid().'/update');

        $this->assertResponseStatusCodeSame(404);
    }
}
