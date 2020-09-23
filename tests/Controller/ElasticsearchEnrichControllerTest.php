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
            $this->assertSelectorTextSame('h1', 'Enrich policies');
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
            $this->assertSelectorTextSame('h1', 'Enrich policies');
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
            $this->assertSelectorTextSame('h1', 'Enrich policies');
            $this->assertSelectorTextSame('h3', 'Create enrich policy');
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

    public function testCreateCopy()
    {
        $this->client->request('GET', '/admin/enrich/create?policy=elasticsearch-admin-test');

        if (false == $this->callManager->hasFeature('enrich')) {
            $this->assertResponseStatusCodeSame(403);
        } else {
            $this->assertResponseStatusCodeSame(200);
            $this->assertPageTitleSame('Enrich policies - Create enrich policy');
            $this->assertSelectorTextSame('h1', 'Enrich policies');
            $this->assertSelectorTextSame('h3', 'Create enrich policy');
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

    public function testRead()
    {
        $this->client->request('GET', '/admin/enrich/elasticsearch-admin-test');

        if (false == $this->callManager->hasFeature('enrich')) {
            $this->assertResponseStatusCodeSame(403);
        } else {
            $this->assertResponseStatusCodeSame(200);
            $this->assertPageTitleSame('Enrich policies - elasticsearch-admin-test');
            $this->assertSelectorTextSame('h1', 'Enrich policies');
        }
    }

    /**
     * @Route("/enrich/{name}/delete", name="enrichs_delete")
     */
    public function testDelete404()
    {
        $this->client->request('GET', '/admin/enrich/'.uniqid().'/delete');

        if (false == $this->callManager->hasFeature('enrich')) {
            $this->assertResponseStatusCodeSame(403);
        } else {
            $this->assertResponseStatusCodeSame(404);
        }
    }

    public function testDelete()
    {
        $this->client->request('GET', '/admin/enrich/elasticsearch-admin-test/delete');

        if (false == $this->callManager->hasFeature('enrich')) {
            $this->assertResponseStatusCodeSame(403);
        } else {
            $this->assertResponseStatusCodeSame(302);
        }
    }
}
