<?php

namespace App\Tests\Controller;

/**
 * @Route("/admin")
 */
class EnrichControllerTest extends AbstractAppControllerTest
{
    /**
     * @Route("/enrich", name="enrich")
     */
    public function testIndex()
    {
        $this->client->request('GET', '/admin/enrich');

        $this->assertResponseStatusCodeSame(200);
        $this->assertPageTitleSame('Enrich policies');
    }

    /**
     * @Route("/enrich/stats", name="enrich_stats")
     */
    public function testStats()
    {
        $this->client->request('GET', '/admin/enrich/stats');

        $this->assertResponseStatusCodeSame(200);
        $this->assertPageTitleSame('Enrich policies - Stats');
    }

    /**
     * @Route("/enrich/create", name="enrich_create")
     */
    public function testCreate()
    {
        $this->client->request('GET', '/admin/enrich/create');

        $this->assertResponseStatusCodeSame(200);
        $this->assertPageTitleSame('Enrich policies - Create enrich policy');
    }

    /**
     * @Route("/enrich/{name}", name="enrich_read")
     */
    public function testRead404()
    {
        $this->client->request('GET', '/admin/enrich/'.uniqid());

        $this->assertResponseStatusCodeSame(404);
    }
}
