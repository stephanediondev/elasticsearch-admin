<?php

namespace App\Tests\Controller;

class EnrichControllerTest extends AbstractAppControllerTest
{
    public function testIndex()
    {
        $this->client->request('GET', '/admin/enrich');

        $this->assertResponseStatusCodeSame(200);
        $this->assertPageTitleSame('Enrich policies');
    }

    public function testStats()
    {
        $this->client->request('GET', '/admin/enrich/stats');

        $this->assertResponseStatusCodeSame(200);
        $this->assertPageTitleSame('Enrich policies - Stats');
    }

    public function testCreate()
    {
        $this->client->request('GET', '/admin/enrich/create');

        $this->assertResponseStatusCodeSame(200);
        $this->assertPageTitleSame('Enrich policies - Create enrich policy');
    }

    public function testRead404()
    {
        $this->client->request('GET', '/_enrich/policy/'.uniqid());

        $this->assertResponseStatusCodeSame(404);
    }
}
