<?php

namespace App\Tests\Controller;

use App\Tests\Controller\AbstractAppControllerTest;

#[Route('/admin')]
class ElasticsearchIlmControllerTest extends AbstractAppControllerTest
{
    #[Route('/ilm', name: 'ilm')]
    public function testIndex(): void
    {
        $this->client->request('GET', '/admin/ilm');

        if (false == $this->callManager->hasFeature('ilm')) {
            $this->assertResponseStatusCodeSame(403);
        } else {
            $this->assertResponseStatusCodeSame(200);
            $this->assertPageTitleSame('ILM policies');
            $this->assertSelectorTextSame('h1', 'Index lifecycle management policies');
            $this->assertSelectorTextContains('h3', 'List');
        }
    }

    #[Route('/ilm/status', name: 'ilm_status')]
    public function testStatus(): void
    {
        $this->client->request('GET', '/admin/ilm/status');

        if (false == $this->callManager->hasFeature('ilm')) {
            $this->assertResponseStatusCodeSame(403);
        } else {
            $this->assertResponseStatusCodeSame(200);
            $this->assertPageTitleSame('ILM policies - Status');
            $this->assertSelectorTextSame('h1', 'Index lifecycle management policies');
            $this->assertSelectorTextSame('h3', 'Status');
        }
    }

    #[Route('/ilm/create', name: 'ilm_create')]
    public function testCreate(): void
    {
        $this->client->request('GET', '/admin/ilm/create');

        if (false == $this->callManager->hasFeature('ilm')) {
            $this->assertResponseStatusCodeSame(403);
        } else {
            $this->assertResponseStatusCodeSame(200);
            $this->assertPageTitleSame('ILM policies - Create ILM policy');
            $this->assertSelectorTextSame('h1', 'Index lifecycle management policies');
            $this->assertSelectorTextSame('h3', 'Create ILM policy');

            $values = [
                'data[name]' => GENERATED_NAME,
                'data[hot_json]' => '{
                    "min_age": "0ms",
                    "actions": {
                        "rollover": {
                            "max_size": "50gb",
                            "max_age": "30d"
                        }
                    }
                }',
            ];
            $this->client->submitForm('Submit', $values);

            $this->assertResponseStatusCodeSame(302);

            $this->client->followRedirect();
            $this->assertPageTitleSame('ILM policies - '.GENERATED_NAME);
            $this->assertSelectorTextSame('h1', 'Index lifecycle management policies');
            $this->assertSelectorTextSame('h2', GENERATED_NAME);
            $this->assertSelectorTextSame('h3', 'Summary');
        }
    }

    public function testCreateCopy404(): void
    {
        $this->client->request('GET', '/admin/ilm/create?policy='.uniqid());

        if (false == $this->callManager->hasFeature('ilm')) {
            $this->assertResponseStatusCodeSame(403);
        } else {
            $this->assertResponseStatusCodeSame(404);
        }
    }

    public function testCreateCopy(): void
    {
        $this->client->request('GET', '/admin/ilm/create?policy='.GENERATED_NAME);

        if (false == $this->callManager->hasFeature('ilm')) {
            $this->assertResponseStatusCodeSame(403);
        } else {
            $this->assertResponseStatusCodeSame(200);
            $this->assertPageTitleSame('ILM policies - Create ILM policy');
            $this->assertSelectorTextSame('h1', 'Index lifecycle management policies');
            $this->assertSelectorTextSame('h3', 'Create ILM policy');

            $this->client->submitForm('Submit');

            $this->assertResponseStatusCodeSame(302);

            $this->client->followRedirect();
            $this->assertPageTitleSame('ILM policies - '.GENERATED_NAME.'-copy');
            $this->assertSelectorTextSame('h1', 'Index lifecycle management policies');
            $this->assertSelectorTextSame('h2', GENERATED_NAME.'-copy');
            $this->assertSelectorTextSame('h3', 'Summary');
        }
    }

    #[Route('/ilm/{name}', name: 'ilm_read')]
    public function testRead404(): void
    {
        $this->client->request('GET', '/admin/ilm/'.uniqid());

        if (false == $this->callManager->hasFeature('ilm')) {
            $this->assertResponseStatusCodeSame(403);
        } else {
            $this->assertResponseStatusCodeSame(404);
        }
    }

    public function testRead(): void
    {
        $this->client->request('GET', '/admin/ilm/'.GENERATED_NAME);

        if (false == $this->callManager->hasFeature('ilm')) {
            $this->assertResponseStatusCodeSame(403);
        } else {
            $this->assertResponseStatusCodeSame(200);
            $this->assertPageTitleSame('ILM policies - '.GENERATED_NAME);
            $this->assertSelectorTextSame('h1', 'Index lifecycle management policies');
            $this->assertSelectorTextSame('h2', GENERATED_NAME);
            $this->assertSelectorTextSame('h3', 'Summary');
        }
    }

    #[Route('/ilm/{name}/update', name: 'ilm_update')]
    public function testUpdate404(): void
    {
        $this->client->request('GET', '/admin/ilm/'.uniqid().'/update');

        if (false == $this->callManager->hasFeature('ilm')) {
            $this->assertResponseStatusCodeSame(403);
        } else {
            $this->assertResponseStatusCodeSame(404);
        }
    }

    public function testUpdate(): void
    {
        $this->client->request('GET', '/admin/ilm/'.GENERATED_NAME.'/update');

        if (false == $this->callManager->hasFeature('ilm')) {
            $this->assertResponseStatusCodeSame(403);
        } else {
            $this->assertResponseStatusCodeSame(200);
            $this->assertPageTitleSame('ILM policies - '.GENERATED_NAME.' - Update');
            $this->assertSelectorTextSame('h1', 'Index lifecycle management policies');
            $this->assertSelectorTextSame('h2', GENERATED_NAME);
            $this->assertSelectorTextSame('h3', 'Update');
        }
    }

    #[Route('/ilm/{name}/apply', name: 'ilm_apply')]
    public function testApply404(): void
    {
        $this->client->request('GET', '/admin/ilm/'.uniqid().'/apply');

        if (false == $this->callManager->hasFeature('ilm')) {
            $this->assertResponseStatusCodeSame(403);
        } else {
            $this->assertResponseStatusCodeSame(404);
        }
    }

    public function testApply(): void
    {
        $this->client->request('GET', '/admin/ilm/'.GENERATED_NAME.'/apply');

        if (false == $this->callManager->hasFeature('ilm')) {
            $this->assertResponseStatusCodeSame(403);
        } else {
            $this->assertResponseStatusCodeSame(200);
            $this->assertPageTitleSame('ILM policies - '.GENERATED_NAME.' - Apply');
            $this->assertSelectorTextSame('h1', 'Index lifecycle management policies');
            $this->assertSelectorTextSame('h2', GENERATED_NAME);
            $this->assertSelectorTextSame('h3', 'Apply');
        }
    }

    #[Route('/ilm/{name}/delete', name: 'ilm_delete')]
    public function testDelete404(): void
    {
        $this->client->request('GET', '/admin/ilm/'.uniqid().'/delete');

        if (false == $this->callManager->hasFeature('ilm')) {
            $this->assertResponseStatusCodeSame(403);
        } else {
            $this->assertResponseStatusCodeSame(404);
        }
    }

    public function testDelete(): void
    {
        $this->client->request('GET', '/admin/ilm/'.GENERATED_NAME.'/delete');

        if (false == $this->callManager->hasFeature('ilm')) {
            $this->assertResponseStatusCodeSame(403);
        } else {
            $this->assertResponseStatusCodeSame(302);
        }
    }

    public function testDeleteCopy(): void
    {
        $this->client->request('GET', '/admin/ilm/'.GENERATED_NAME.'-copy/delete');

        if (false == $this->callManager->hasFeature('ilm')) {
            $this->assertResponseStatusCodeSame(403);
        } else {
            $this->assertResponseStatusCodeSame(302);
        }
    }
}
