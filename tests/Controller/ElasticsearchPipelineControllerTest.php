<?php

namespace App\Tests\Controller;

/**
 * @Route("/admin")
 */
class ElasticsearchPipelineControllerTest extends AbstractAppControllerTest
{
    /**
     * @Route("/pipelines", name="pipelines")
     */
    public function testIndex()
    {
        $this->client->request('GET', '/admin/pipelines');

        if (false == $this->callManager->hasFeature('pipelines')) {
            $this->assertResponseStatusCodeSame(403);
        } else {
            $this->assertResponseStatusCodeSame(200);
            $this->assertPageTitleSame('Pipelines');
        }
    }

    /**
     * @Route("/pipelines/create", name="pipelines_create")
     */
    public function testCreate()
    {
        $this->client->request('GET', '/admin/pipelines/create');

        if (false == $this->callManager->hasFeature('pipelines')) {
            $this->assertResponseStatusCodeSame(403);
        } else {
            $this->assertResponseStatusCodeSame(200);
            $this->assertPageTitleSame('Pipelines - Create pipeline');

            $values = [
                'data[name]' => GENERATED_NAME,
                'data[processors]' => '[]',
            ];
            $this->client->submitForm('Submit', $values);

            $this->assertResponseStatusCodeSame(302);

            $this->client->followRedirect();
            $this->assertPageTitleSame('Pipelines - '.GENERATED_NAME);
        }
    }

    public function testCreateCopy404()
    {
        $this->client->request('GET', '/admin/pipelines/create?pipeline='.uniqid());

        if (false == $this->callManager->hasFeature('pipelines')) {
            $this->assertResponseStatusCodeSame(403);
        } else {
            $this->assertResponseStatusCodeSame(404);
        }
    }

    public function testCreateCopy()
    {
        $this->client->request('GET', '/admin/pipelines/create?pipeline='.GENERATED_NAME);

        if (false == $this->callManager->hasFeature('pipelines')) {
            $this->assertResponseStatusCodeSame(403);
        } else {
            $this->assertResponseStatusCodeSame(200);
            $this->assertPageTitleSame('Pipelines - Create pipeline');

            $values = [
                'data[processors]' => '[]',
            ];
            $this->client->submitForm('Submit', $values);

            $this->assertResponseStatusCodeSame(302);

            $this->client->followRedirect();
            $this->assertPageTitleSame('Pipelines - '.GENERATED_NAME.'-copy');
        }
    }

    /**
     * @Route("/pipelines/{name}", name="pipelines_read")
     */
    public function testRead404()
    {
        $this->client->request('GET', '/admin/pipelines/'.uniqid());

        if (false == $this->callManager->hasFeature('pipelines')) {
            $this->assertResponseStatusCodeSame(403);
        } else {
            $this->assertResponseStatusCodeSame(404);
        }
    }

    public function testRead()
    {
        $this->client->request('GET', '/admin/pipelines/'.GENERATED_NAME);

        if (false == $this->callManager->hasFeature('pipelines')) {
            $this->assertResponseStatusCodeSame(403);
        } else {
            $this->assertResponseStatusCodeSame(200);
            $this->assertPageTitleSame('Pipelines - '.GENERATED_NAME);
        }
    }

    /**
     * @Route("/pipelines/{name}/update", name="pipelines_update")
     */
    public function testUpdate404()
    {
        $this->client->request('GET', '/admin/pipelines/'.uniqid().'/update');

        if (false == $this->callManager->hasFeature('pipelines')) {
            $this->assertResponseStatusCodeSame(403);
        } else {
            $this->assertResponseStatusCodeSame(404);
        }
    }

    public function testUpdate()
    {
        $this->client->request('GET', '/admin/pipelines/'.GENERATED_NAME.'/update');

        if (false == $this->callManager->hasFeature('pipelines')) {
            $this->assertResponseStatusCodeSame(403);
        } else {
            $this->assertResponseStatusCodeSame(200);
            $this->assertPageTitleSame('Pipelines - '.GENERATED_NAME.' - Update');
        }
    }

    /**
     * @Route("/pipelines/{name}/delete", name="pipelines_delete")
     */
    public function testDelete404()
    {
        $this->client->request('GET', '/admin/pipelines/'.uniqid().'/delete');

        if (false == $this->callManager->hasFeature('pipelines')) {
            $this->assertResponseStatusCodeSame(403);
        } else {
            $this->assertResponseStatusCodeSame(404);
        }
    }

    public function testDelete()
    {
        $this->client->request('GET', '/admin/pipelines/'.GENERATED_NAME.'/delete');

        if (false == $this->callManager->hasFeature('pipelines')) {
            $this->assertResponseStatusCodeSame(403);
        } else {
            $this->assertResponseStatusCodeSame(302);
        }
    }

    public function testDeleteCopy()
    {
        $this->client->request('GET', '/admin/pipelines/'.GENERATED_NAME.'-copy/delete');

        if (false == $this->callManager->hasFeature('pipelines')) {
            $this->assertResponseStatusCodeSame(403);
        } else {
            $this->assertResponseStatusCodeSame(302);
        }
    }
}
