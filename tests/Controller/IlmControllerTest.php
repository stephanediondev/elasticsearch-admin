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

        if (true == $this->callManager->hasFeature('ilm')) {
            $this->assertResponseStatusCodeSame(200);
            $this->assertPageTitleSame('ILM policies');
        } else {
            $this->assertResponseStatusCodeSame(403);
        }
    }

    /**
     * @Route("/ilm/status", name="ilm_status")
     */
    public function testStatus()
    {
        $this->client->request('GET', '/admin/ilm/status');

        if (true == $this->callManager->hasFeature('ilm')) {
            $this->assertResponseStatusCodeSame(200);
            $this->assertPageTitleSame('ILM policies - Status');
        } else {
            $this->assertResponseStatusCodeSame(403);
        }
    }

    /**
     * @Route("/ilm/create", name="ilm_create")
     */
    public function testCreate()
    {
        $this->client->request('GET', '/admin/ilm/create');

        if (true == $this->callManager->hasFeature('ilm')) {
            $this->assertResponseStatusCodeSame(200);
            $this->assertPageTitleSame('ILM policies - Create ILM policy');
        } else {
            $this->assertResponseStatusCodeSame(403);
        }
    }

    public function testCreateCopy404()
    {
        $this->client->request('GET', '/admin/ilm/create?policy='.uniqid());

        if (true == $this->callManager->hasFeature('ilm')) {
            $this->assertResponseStatusCodeSame(404);
        } else {
            $this->assertResponseStatusCodeSame(403);
        }
    }

    /**
     * @Route("/ilm/{name}", name="ilm_read")
     */
    public function testRead404()
    {
        $this->client->request('GET', '/admin/ilm/'.uniqid());

        if (true == $this->callManager->hasFeature('ilm')) {
            $this->assertResponseStatusCodeSame(404);
        } else {
            $this->assertResponseStatusCodeSame(403);
        }
    }
}
