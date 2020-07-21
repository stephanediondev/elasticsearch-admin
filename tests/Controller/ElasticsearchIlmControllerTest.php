<?php

namespace App\Tests\Controller;

/**
 * @Route("/admin")
 */
class ElasticsearchIlmControllerTest extends AbstractAppControllerTest
{
    /**
     * @Route("/ilm", name="ilm")
     */
    public function testIndex()
    {
        $this->client->request('GET', '/admin/ilm');

        if (false == $this->callManager->hasFeature('ilm')) {
            $this->assertResponseStatusCodeSame(403);
        } else {
            $this->assertResponseStatusCodeSame(200);
            $this->assertPageTitleSame('ILM policies');
        }
    }

    /**
     * @Route("/ilm/status", name="ilm_status")
     */
    public function testStatus()
    {
        $this->client->request('GET', '/admin/ilm/status');

        if (false == $this->callManager->hasFeature('ilm')) {
            $this->assertResponseStatusCodeSame(403);
        } else {
            $this->assertResponseStatusCodeSame(200);
            $this->assertPageTitleSame('ILM policies - Status');
        }
    }

    /**
     * @Route("/ilm/create", name="ilm_create")
     */
    public function testCreate()
    {
        $this->client->request('GET', '/admin/ilm/create');

        if (false == $this->callManager->hasFeature('ilm')) {
            $this->assertResponseStatusCodeSame(403);
        } else {
            $this->assertResponseStatusCodeSame(200);
            $this->assertPageTitleSame('ILM policies - Create ILM policy');
        }
    }

    public function testCreateCopy404()
    {
        $this->client->request('GET', '/admin/ilm/create?policy='.uniqid());

        if (false == $this->callManager->hasFeature('ilm')) {
            $this->assertResponseStatusCodeSame(403);
        } else {
            $this->assertResponseStatusCodeSame(404);
        }
    }

    public function testCreateCopy()
    {
        $this->client->request('GET', '/admin/ilm/create?policy=elasticsearch-admin-test');

        if (false == $this->callManager->hasFeature('ilm')) {
            $this->assertResponseStatusCodeSame(403);
        } else {
            $this->assertResponseStatusCodeSame(200);
            $this->assertPageTitleSame('ILM policies - Create ILM policy');
        }
    }

    /**
     * @Route("/ilm/{name}", name="ilm_read")
     */
    public function testRead404()
    {
        $this->client->request('GET', '/admin/ilm/'.uniqid());

        if (false == $this->callManager->hasFeature('ilm')) {
            $this->assertResponseStatusCodeSame(403);
        } else {
            $this->assertResponseStatusCodeSame(404);
        }
    }

    public function testRead()
    {
        $this->client->request('GET', '/admin/ilm/elasticsearch-admin-test');

        if (false == $this->callManager->hasFeature('ilm')) {
            $this->assertResponseStatusCodeSame(403);
        } else {
            $this->assertResponseStatusCodeSame(200);
            $this->assertPageTitleSame('ILM policies - elasticsearch-admin-test');
        }
    }

    /**
     * @Route("/ilm/{name}/update", name="ilm_update")
     */
    public function testUpdate404()
    {
        $this->client->request('GET', '/admin/ilm/'.uniqid().'/update');

        if (false == $this->callManager->hasFeature('ilm')) {
            $this->assertResponseStatusCodeSame(403);
        } else {
            $this->assertResponseStatusCodeSame(404);
        }
    }

    public function testUpdate()
    {
        $this->client->request('GET', '/admin/ilm/elasticsearch-admin-test/update');

        if (false == $this->callManager->hasFeature('ilm')) {
            $this->assertResponseStatusCodeSame(403);
        } else {
            $this->assertResponseStatusCodeSame(200);
            $this->assertPageTitleSame('ILM policies - elasticsearch-admin-test - Update');
        }
    }

    /**
     * @Route("/ilm/{name}/delete", name="ilm_delete")
     */
    public function testDelete404()
    {
        $this->client->request('GET', '/admin/ilm/'.uniqid().'/delete');

        if (false == $this->callManager->hasFeature('composable_template')) {
            $this->assertResponseStatusCodeSame(403);
        } else {
            $this->assertResponseStatusCodeSame(404);
        }
    }

    public function testDelete()
    {
        $this->client->request('GET', '/admin/ilm/elasticsearch-admin-test/delete');

        if (false == $this->callManager->hasFeature('ilm')) {
            $this->assertResponseStatusCodeSame(403);
        } else {
            $this->assertResponseStatusCodeSame(302);
        }
    }
}
