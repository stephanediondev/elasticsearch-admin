<?php

namespace App\Tests\Controller;

/**
 * @Route("/admin")
 */
class ElasticsearchSlmControllerTest extends AbstractAppControllerTest
{
    /**
     * @Route("/slm", name="slm")
     */
    public function testIndex()
    {
        $this->client->request('GET', '/admin/slm');

        if (false == $this->callManager->hasFeature('slm')) {
            $this->assertResponseStatusCodeSame(403);
        } else {
            $this->assertResponseStatusCodeSame(200);
            $this->assertPageTitleSame('SLM policies');
        }
    }

    /**
     * @Route("/slm/stats", name="slm_stats")
     */
    public function testStats()
    {
        $this->client->request('GET', '/admin/slm/stats');

        if (false == $this->callManager->hasFeature('slm')) {
            $this->assertResponseStatusCodeSame(403);
        } else {
            $this->assertResponseStatusCodeSame(200);
            $this->assertPageTitleSame('SLM policies - Stats');
        }
    }

    /**
     * @Route("/slm/status", name="slm_status")
     */
    public function testStatus()
    {
        $this->client->request('GET', '/admin/slm/status');

        if (false == $this->callManager->hasFeature('slm')) {
            $this->assertResponseStatusCodeSame(403);
        } else {
            $this->assertResponseStatusCodeSame(200);
            $this->assertPageTitleSame('SLM policies - Status');
        }
    }

    /**
     * @Route("/slm/create", name="slm_create")
     */
    public function testCreate()
    {
        $this->client->request('GET', '/admin/slm/create');

        if (false == $this->callManager->hasFeature('slm')) {
            $this->assertResponseStatusCodeSame(403);
        } else {
            $this->assertResponseStatusCodeSame(200);
            $this->assertPageTitleSame('SLM policies - Create SLM policy');
        }
    }

    public function testCreateCopy404()
    {
        $this->client->request('GET', '/admin/slm/create?policy='.uniqid());

        if (false == $this->callManager->hasFeature('slm')) {
            $this->assertResponseStatusCodeSame(403);
        } else {
            $this->assertResponseStatusCodeSame(404);
        }
    }

    /*public function testCreateCopy()
    {
        $this->client->request('GET', '/admin/slm/create?policy='.GENERATED_NAME);

        if (false == $this->callManager->hasFeature('slm')) {
            $this->assertResponseStatusCodeSame(403);
        } else {
            $this->assertResponseStatusCodeSame(200);
            $this->assertPageTitleSame('SLM policies - Create SLM policy');
        }
    }*/

    /**
     * @Route("/slm/{name}", name="slm_read")
     */
    public function testRead404()
    {
        $this->client->request('GET', '/admin/slm/'.uniqid());

        if (false == $this->callManager->hasFeature('slm')) {
            $this->assertResponseStatusCodeSame(403);
        } else {
            $this->assertResponseStatusCodeSame(404);
        }
    }

    /*public function testRead()
    {
        $this->client->request('GET', '/admin/slm/'.GENERATED_NAME);

        if (false == $this->callManager->hasFeature('slm')) {
            $this->assertResponseStatusCodeSame(403);
        } else {
            $this->assertResponseStatusCodeSame(200);
            $this->assertPageTitleSame('SLM policies - '.GENERATED_NAME);
        }
    }*/

    /**
     * @Route("/slm/{name}/history", name="slm_read_history")
     */
    public function testReadHistory404()
    {
        $this->client->request('GET', '/admin/slm/'.uniqid().'/history');

        if (false == $this->callManager->hasFeature('slm')) {
            $this->assertResponseStatusCodeSame(403);
        } else {
            $this->assertResponseStatusCodeSame(404);
        }
    }

    /**
     * @Route("/slm/{name}/stats", name="slm_read_stats")
     */
    public function testReadStats404()
    {
        $this->client->request('GET', '/admin/slm/'.uniqid().'/stats');

        if (false == $this->callManager->hasFeature('slm')) {
            $this->assertResponseStatusCodeSame(403);
        } else {
            $this->assertResponseStatusCodeSame(404);
        }
    }

    /**
     * @Route("/slm/{name}/update", name="slm_update")
     */
    public function testUpdate404()
    {
        $this->client->request('GET', '/admin/slm/'.uniqid().'/update');

        if (false == $this->callManager->hasFeature('slm')) {
            $this->assertResponseStatusCodeSame(403);
        } else {
            $this->assertResponseStatusCodeSame(404);
        }
    }

    /*public function testUpdate()
    {
        $this->client->request('GET', '/admin/slm/'.GENERATED_NAME.'/update');

        if (false == $this->callManager->hasFeature('slm')) {
            $this->assertResponseStatusCodeSame(403);
        } else {
            $this->assertResponseStatusCodeSame(200);
            $this->assertPageTitleSame('SLM policies - '.GENERATED_NAME.' - Update');
        }
    }*/

    /**
     * @Route("/slm/{name}/delete", name="slm_delete")
     */
    public function testDelete404()
    {
        $this->client->request('GET', '/admin/slm/'.uniqid().'/delete');

        if (false == $this->callManager->hasFeature('composable_template')) {
            $this->assertResponseStatusCodeSame(403);
        } else {
            $this->assertResponseStatusCodeSame(404);
        }
    }

    /*public function testDelete()
    {
        $this->client->request('GET', '/admin/slm/'.GENERATED_NAME.'/delete');

        if (false == $this->callManager->hasFeature('slm')) {
            $this->assertResponseStatusCodeSame(403);
        } else {
            $this->assertResponseStatusCodeSame(302);
        }
    }*/
}
