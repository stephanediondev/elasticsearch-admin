<?php

namespace App\Tests\Controller;

class SlmControllerTest extends AbstractAppControllerTest
{
    public function testIndex()
    {
        $this->client->request('GET', '/admin/slm');

        $this->assertResponseStatusCodeSame(200);
        $this->assertPageTitleSame('SLM policies');
    }

    public function testStatus()
    {
        $this->client->request('GET', '/admin/slm/status');

        $this->assertResponseStatusCodeSame(200);
        $this->assertPageTitleSame('SLM policies - Status');
    }

    public function testStats()
    {
        $this->client->request('GET', '/admin/slm/stats');

        $this->assertResponseStatusCodeSame(200);
        $this->assertPageTitleSame('SLM policies - Stats');
    }

    public function testCreate()
    {
        $this->client->request('GET', '/admin/slm/create');

        $this->assertResponseStatusCodeSame(200);
        $this->assertPageTitleSame('SLM policies - Create SLM policy');
    }

    public function testRead404()
    {
        $this->client->request('GET', '/admin/slm/'.uniqid());

        $this->assertResponseStatusCodeSame(404);
    }

    public function testReadHistory404()
    {
        $this->client->request('GET', '/admin/slm/'.uniqid().'/history');

        $this->assertResponseStatusCodeSame(404);
    }

    public function testReadStats404()
    {
        $this->client->request('GET', '/admin/slm/'.uniqid().'/stats');

        $this->assertResponseStatusCodeSame(404);
    }

    public function testUpdate404()
    {
        $this->client->request('GET', '/admin/slm/'.uniqid().'/update');

        $this->assertResponseStatusCodeSame(404);
    }
}
