<?php

namespace App\Tests\Controller;

/**
 * @Route("/admin")
 */
class ElasticsearchEnrichControllerTest extends AbstractAppControllerTest
{
    /**
     * @Route("/enrich", name="enrich")
     */
    public function testIndex()
    {
        $this->client->request('GET', '/admin/enrich');

        if (false == $this->callManager->hasFeature('enrich')) {
            $this->assertResponseStatusCodeSame(403);
        } else {
            $this->assertResponseStatusCodeSame(200);
            $this->assertPageTitleSame('Enrich policies');
        }
    }

    /**
     * @Route("/enrich/stats", name="enrich_stats")
     */
    public function testStats()
    {
        $this->client->request('GET', '/admin/enrich/stats');

        if (false == $this->callManager->hasFeature('enrich')) {
            $this->assertResponseStatusCodeSame(403);
        } else {
            $this->assertResponseStatusCodeSame(200);
            $this->assertPageTitleSame('Enrich policies - Stats');
        }
    }

    /**
     * @Route("/enrich/create", name="enrich_create")
     */
    public function testCreate()
    {
        $this->client->request('GET', '/admin/enrich/create');

        if (false == $this->callManager->hasFeature('enrich')) {
            $this->assertResponseStatusCodeSame(403);
        } else {
            $this->assertResponseStatusCodeSame(200);
            $this->assertPageTitleSame('Enrich policies - Create enrich policy');
        }
    }

    public function testCreateCopy404()
    {
        $this->client->request('GET', '/admin/enrich/create?policy='.uniqid());

        if (false == $this->callManager->hasFeature('enrich')) {
            $this->assertResponseStatusCodeSame(403);
        } else {
            $this->assertResponseStatusCodeSame(404);
        }
    }

    /**
     * @Route("/enrich/{name}", name="enrich_read")
     */
    public function testRead404()
    {
        $this->client->request('GET', '/admin/enrich/'.uniqid());

        if (false == $this->callManager->hasFeature('enrich')) {
            $this->assertResponseStatusCodeSame(403);
        } else {
            $this->assertResponseStatusCodeSame(404);
        }
    }

    /*public function testRead()
    {
        $this->client->request('GET', '/admin/enrich/elasticsearch-admin-test');

        if (false == $this->callManager->hasFeature('enrich')) {
            $this->assertResponseStatusCodeSame(403);
        } else {
            $this->assertResponseStatusCodeSame(200);
            $this->assertPageTitleSame('Enrich policies - elasticsearch-admin-test');
        }
    }*/
}
