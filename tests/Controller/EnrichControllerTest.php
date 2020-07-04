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

        if (true == $this->hasFeature('enrich')) {
            $this->assertResponseStatusCodeSame(200);
            $this->assertPageTitleSame('Enrich policies');
        } else {
            $this->assertResponseStatusCodeSame(403);
        }
    }

    /**
     * @Route("/enrich/stats", name="enrich_stats")
     */
    public function testStats()
    {
        $this->client->request('GET', '/admin/enrich/stats');

        if (true == $this->hasFeature('enrich')) {
            $this->assertResponseStatusCodeSame(200);
            $this->assertPageTitleSame('Enrich policies - Stats');
        } else {
            $this->assertResponseStatusCodeSame(403);
        }
    }

    /**
     * @Route("/enrich/create", name="enrich_create")
     */
    public function testCreate()
    {
        $this->client->request('GET', '/admin/enrich/create');

        if (true == $this->hasFeature('enrich')) {
            $this->assertResponseStatusCodeSame(200);
            $this->assertPageTitleSame('Enrich policies - Create enrich policy');
        } else {
            $this->assertResponseStatusCodeSame(403);
        }
    }

    public function testCreateCopy404()
    {
        $this->client->request('GET', '/admin/enrich/create?policy='.uniqid());

        $this->assertResponseStatusCodeSame(404);
    }

    /**
     * @Route("/enrich/{name}", name="enrich_read")
     */
    public function testRead404()
    {
        $this->client->request('GET', '/admin/enrich/'.uniqid());

        if (true == $this->hasFeature('enrich')) {
            $this->assertResponseStatusCodeSame(404);
        } else {
            $this->assertResponseStatusCodeSame(403);
        }
    }
}
