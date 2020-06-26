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

        if (true == isset($this->xpack['features']['enrich']) && true == $this->xpack['features']['enrich']['enabled']) {
            $this->assertResponseStatusCodeSame(200);
            $this->assertPageTitleSame('Enrich policies');
        } else {
            $this->assertResponseStatusCodeSame(500);
        }
    }

    /**
     * @Route("/enrich/stats", name="enrich_stats")
     */
    public function testStats()
    {
        $this->client->request('GET', '/admin/enrich/stats');

        if (true == isset($this->xpack['features']['enrich']) && true == $this->xpack['features']['enrich']['enabled']) {
            $this->assertResponseStatusCodeSame(200);
            $this->assertPageTitleSame('Enrich policies - Stats');
        } else {
            $this->assertResponseStatusCodeSame(500);
        }
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
