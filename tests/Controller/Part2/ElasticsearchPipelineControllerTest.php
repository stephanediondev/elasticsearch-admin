<?php

namespace App\Tests\Controller\Part2;

use App\Tests\Controller\AbstractAppControllerTest;

/**
 * @Route("/admin")
 */
class ElasticsearchPipelineControllerTest extends AbstractAppControllerTest
{
    /**
     * @Route("/pipelines", name="pipelines")
     */
    public function testIndex(): void
    {
        $this->client->request('GET', '/admin/pipelines');

        if (false == $this->callManager->hasFeature('pipelines')) {
            $this->assertResponseStatusCodeSame(403);
        } else {
            $this->assertResponseStatusCodeSame(200);
            $this->assertPageTitleSame('Pipelines');
            $this->assertSelectorTextSame('h1', 'Pipelines');
            $this->assertSelectorTextContains('h3', 'List');
        }
    }

    /**
     * @Route("/pipelines/create", name="pipelines_create")
     */
    public function testCreate(): void
    {
        $this->client->request('GET', '/admin/pipelines/create');

        if (false == $this->callManager->hasFeature('pipelines')) {
            $this->assertResponseStatusCodeSame(403);
        } else {
            $this->assertResponseStatusCodeSame(200);
            $this->assertPageTitleSame('Pipelines - Create pipeline');
            $this->assertSelectorTextSame('h1', 'Pipelines');
            $this->assertSelectorTextSame('h3', 'Create pipeline');

            $values = [
                'data[name]' => GENERATED_NAME,
                'data[processors_json]' => '[]',
            ];
            $this->client->submitForm('Submit', $values);

            $this->assertResponseStatusCodeSame(302);

            $this->client->followRedirect();
            $this->assertPageTitleSame('Pipelines - '.GENERATED_NAME);
            $this->assertSelectorTextSame('h1', 'Pipelines');
            $this->assertSelectorTextSame('h2', GENERATED_NAME);
            $this->assertSelectorTextSame('h3', 'Summary');
        }
    }

    public function testCreateCopy404(): void
    {
        $this->client->request('GET', '/admin/pipelines/create?pipeline='.uniqid());

        if (false == $this->callManager->hasFeature('pipelines')) {
            $this->assertResponseStatusCodeSame(403);
        } else {
            $this->assertResponseStatusCodeSame(404);
        }
    }

    public function testCreateCopy(): void
    {
        $this->client->request('GET', '/admin/pipelines/create?pipeline='.GENERATED_NAME);

        if (false == $this->callManager->hasFeature('pipelines')) {
            $this->assertResponseStatusCodeSame(403);
        } else {
            $this->assertResponseStatusCodeSame(200);
            $this->assertPageTitleSame('Pipelines - Create pipeline');
            $this->assertSelectorTextSame('h1', 'Pipelines');
            $this->assertSelectorTextSame('h3', 'Create pipeline');

            $values = [
                'data[processors_json]' => '[]',
            ];
            $this->client->submitForm('Submit', $values);

            $this->assertResponseStatusCodeSame(302);

            $this->client->followRedirect();
            $this->assertPageTitleSame('Pipelines - '.GENERATED_NAME.'-copy');
            $this->assertSelectorTextSame('h1', 'Pipelines');
            $this->assertSelectorTextSame('h2', GENERATED_NAME.'-copy');
            $this->assertSelectorTextSame('h3', 'Summary');
        }
    }

    /**
     * @Route("/pipelines/{name}", name="pipelines_read")
     */
    public function testRead404(): void
    {
        $this->client->request('GET', '/admin/pipelines/'.uniqid());

        if (false == $this->callManager->hasFeature('pipelines')) {
            $this->assertResponseStatusCodeSame(403);
        } else {
            $this->assertResponseStatusCodeSame(404);
        }
    }

    public function testRead(): void
    {
        $this->client->request('GET', '/admin/pipelines/'.GENERATED_NAME);

        if (false == $this->callManager->hasFeature('pipelines')) {
            $this->assertResponseStatusCodeSame(403);
        } else {
            $this->assertResponseStatusCodeSame(200);
            $this->assertPageTitleSame('Pipelines - '.GENERATED_NAME);
            $this->assertSelectorTextSame('h1', 'Pipelines');
            $this->assertSelectorTextSame('h2', GENERATED_NAME);
            $this->assertSelectorTextSame('h3', 'Summary');
        }
    }

    /**
     * @Route("/pipelines/{name}/update", name="pipelines_update")
     */
    public function testUpdate404(): void
    {
        $this->client->request('GET', '/admin/pipelines/'.uniqid().'/update');

        if (false == $this->callManager->hasFeature('pipelines')) {
            $this->assertResponseStatusCodeSame(403);
        } else {
            $this->assertResponseStatusCodeSame(404);
        }
    }

    public function testUpdate(): void
    {
        $this->client->request('GET', '/admin/pipelines/'.GENERATED_NAME.'/update');

        if (false == $this->callManager->hasFeature('pipelines')) {
            $this->assertResponseStatusCodeSame(403);
        } else {
            $this->assertResponseStatusCodeSame(200);
            $this->assertPageTitleSame('Pipelines - '.GENERATED_NAME.' - Update');
            $this->assertSelectorTextSame('h1', 'Pipelines');
            $this->assertSelectorTextSame('h2', GENERATED_NAME);
            $this->assertSelectorTextSame('h3', 'Update');
        }
    }

    /**
     * @Route("/pipelines/{name}/delete", name="pipelines_delete")
     */
    public function testDelete404(): void
    {
        $this->client->request('GET', '/admin/pipelines/'.uniqid().'/delete');

        if (false == $this->callManager->hasFeature('pipelines')) {
            $this->assertResponseStatusCodeSame(403);
        } else {
            $this->assertResponseStatusCodeSame(404);
        }
    }

    public function testDelete(): void
    {
        $this->client->request('GET', '/admin/pipelines/'.GENERATED_NAME.'/delete');

        if (false == $this->callManager->hasFeature('pipelines')) {
            $this->assertResponseStatusCodeSame(403);
        } else {
            $this->assertResponseStatusCodeSame(302);
        }
    }

    public function testDeleteCopy(): void
    {
        $this->client->request('GET', '/admin/pipelines/'.GENERATED_NAME.'-copy/delete');

        if (false == $this->callManager->hasFeature('pipelines')) {
            $this->assertResponseStatusCodeSame(403);
        } else {
            $this->assertResponseStatusCodeSame(302);
        }
    }
}
