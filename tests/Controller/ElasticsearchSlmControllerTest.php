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
    public function testIndex(): void
    {
        $this->client->request('GET', '/admin/slm');

        if (false == $this->callManager->hasFeature('slm')) {
            $this->assertResponseStatusCodeSame(403);
        } else {
            $this->assertResponseStatusCodeSame(200);
            $this->assertPageTitleSame('SLM policies');
            $this->assertSelectorTextSame('h1', 'Snapshot lifecycle management policies');
            $this->assertSelectorTextContains('h3', 'List');
        }
    }

    /**
     * @Route("/slm/stats", name="slm_stats")
     */
    public function testStats(): void
    {
        $this->client->request('GET', '/admin/slm/stats');

        if (false == $this->callManager->hasFeature('slm')) {
            $this->assertResponseStatusCodeSame(403);
        } else {
            $this->assertResponseStatusCodeSame(200);
            $this->assertPageTitleSame('SLM policies - Stats');
            $this->assertSelectorTextSame('h1', 'Snapshot lifecycle management policies');
            $this->assertSelectorTextSame('h3', 'Stats');
        }
    }

    /**
     * @Route("/slm/status", name="slm_status")
     */
    public function testStatus(): void
    {
        $this->client->request('GET', '/admin/slm/status');

        if (false == $this->callManager->hasFeature('slm')) {
            $this->assertResponseStatusCodeSame(403);
        } else {
            $this->assertResponseStatusCodeSame(200);
            $this->assertPageTitleSame('SLM policies - Status');
            $this->assertSelectorTextSame('h1', 'Snapshot lifecycle management policies');
            $this->assertSelectorTextSame('h3', 'Status');
        }
    }

    /**
     * @Route("/slm/create", name="slm_create")
     */
    public function testCreate(): void
    {
        $this->client->request('GET', '/admin/slm/create');

        if (false == $this->callManager->hasFeature('slm')) {
            $this->assertResponseStatusCodeSame(403);
        } else {
            $this->assertResponseStatusCodeSame(200);
            $this->assertPageTitleSame('SLM policies - Create SLM policy');
            $this->assertSelectorTextSame('h1', 'Snapshot lifecycle management policies');
            $this->assertSelectorTextSame('h3', 'Create SLM policy');
        }
    }

    public function testCreateCopy404(): void
    {
        $this->client->request('GET', '/admin/slm/create?policy='.uniqid());

        if (false == $this->callManager->hasFeature('slm')) {
            $this->assertResponseStatusCodeSame(403);
        } else {
            $this->assertResponseStatusCodeSame(404);
        }
    }

    /*public function testCreateCopy(): void
    {
        $this->client->request('GET', '/admin/slm/create?policy='.GENERATED_NAME);

        if (false == $this->callManager->hasFeature('slm')) {
            $this->assertResponseStatusCodeSame(403);
        } else {
            $this->assertResponseStatusCodeSame(200);
            $this->assertPageTitleSame('SLM policies - Create SLM policy');
            $this->assertSelectorTextSame('h1', 'Snapshot lifecycle management policies');
            $this->assertSelectorTextSame('h3', 'Create SLM policy');
        }
    }*/

    /**
     * @Route("/slm/{name}", name="slm_read")
     */
    public function testRead404(): void
    {
        $this->client->request('GET', '/admin/slm/'.uniqid());

        if (false == $this->callManager->hasFeature('slm')) {
            $this->assertResponseStatusCodeSame(403);
        } else {
            $this->assertResponseStatusCodeSame(404);
        }
    }

    /*public function testRead(): void
    {
        $this->client->request('GET', '/admin/slm/'.GENERATED_NAME);

        if (false == $this->callManager->hasFeature('slm')) {
            $this->assertResponseStatusCodeSame(403);
        } else {
            $this->assertResponseStatusCodeSame(200);
            $this->assertPageTitleSame('SLM policies - '.GENERATED_NAME);
            $this->assertSelectorTextSame('h1', 'Snapshot lifecycle management policies');
            $this->assertSelectorTextSame('h2', GENERATED_NAME);
            $this->assertSelectorTextSame('h3', 'Summary');
        }
    }*/

    /**
     * @Route("/slm/{name}/history", name="slm_read_history")
     */
    public function testReadHistory404(): void
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
    public function testReadStats404(): void
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
    public function testUpdate404(): void
    {
        $this->client->request('GET', '/admin/slm/'.uniqid().'/update');

        if (false == $this->callManager->hasFeature('slm')) {
            $this->assertResponseStatusCodeSame(403);
        } else {
            $this->assertResponseStatusCodeSame(404);
        }
    }

    /*public function testUpdate(): void
    {
        $this->client->request('GET', '/admin/slm/'.GENERATED_NAME.'/update');

        if (false == $this->callManager->hasFeature('slm')) {
            $this->assertResponseStatusCodeSame(403);
        } else {
            $this->assertResponseStatusCodeSame(200);
            $this->assertPageTitleSame('SLM policies - '.GENERATED_NAME.' - Update');
            $this->assertSelectorTextSame('h1', 'Snapshot lifecycle management policies');
            $this->assertSelectorTextSame('h2', GENERATED_NAME);
            $this->assertSelectorTextSame('h3', 'Update');
        }
    }*/

    /**
     * @Route("/slm/{name}/delete", name="slm_delete")
     */
    public function testDelete404(): void
    {
        $this->client->request('GET', '/admin/slm/'.uniqid().'/delete');

        if (false == $this->callManager->hasFeature('composable_template')) {
            $this->assertResponseStatusCodeSame(403);
        } else {
            $this->assertResponseStatusCodeSame(404);
        }
    }

    /*public function testDelete(): void
    {
        $this->client->request('GET', '/admin/slm/'.GENERATED_NAME.'/delete');

        if (false == $this->callManager->hasFeature('slm')) {
            $this->assertResponseStatusCodeSame(403);
        } else {
            $this->assertResponseStatusCodeSame(302);
        }
    }*/
}
