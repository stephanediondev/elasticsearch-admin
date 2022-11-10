<?php

namespace App\Tests\Controller;

use App\Tests\Controller\AbstractAppControllerTest;

class ElasticsearchRoleControllerTest extends AbstractAppControllerTest
{
    public function testIndex(): void
    {
        $this->client->request('GET', '/admin/elasticsearch-roles');

        if (false == $this->callManager->hasFeature('security')) {
            $this->assertResponseStatusCodeSame(403);
        } else {
            $this->assertResponseStatusCodeSame(200);
            $this->assertPageTitleSame('Roles');
            $this->assertSelectorTextSame('h1', 'Roles');
            $this->assertSelectorTextContains('h3', 'List');
        }
    }

    public function testCreate(): void
    {
        $this->client->request('GET', '/admin/elasticsearch-roles/create');

        if (false == $this->callManager->hasFeature('security')) {
            $this->assertResponseStatusCodeSame(403);
        } else {
            $this->assertResponseStatusCodeSame(200);
            $this->assertPageTitleSame('Roles - Create role');
            $this->assertSelectorTextSame('h1', 'Roles');
            $this->assertSelectorTextSame('h3', 'Create role');

            $values = [
                'data[name]' => GENERATED_NAME,
                'data[cluster]' => ['all'],
                'data[run_as]' => [],
            ];
            $this->client->submitForm('Submit', $values);

            $this->assertResponseStatusCodeSame(302);

            $this->client->followRedirect();
            $this->assertPageTitleSame('Roles - '.GENERATED_NAME);
            $this->assertSelectorTextSame('h1', 'Roles');
            $this->assertSelectorTextSame('h2', GENERATED_NAME);
            $this->assertSelectorTextSame('h3', 'Summary');
        }
    }

    public function testCreateCopy404(): void
    {
        $this->client->request('GET', '/admin/elasticsearch-roles/create?role='.uniqid());

        if (false == $this->callManager->hasFeature('security')) {
            $this->assertResponseStatusCodeSame(403);
        } else {
            $this->assertResponseStatusCodeSame(404);
        }
    }

    public function testCreateCopy403(): void
    {
        $this->client->request('GET', '/admin/elasticsearch-roles/create?role=superuser');

        if (false == $this->callManager->hasFeature('security')) {
            $this->assertResponseStatusCodeSame(403);
        } else {
            $this->assertResponseStatusCodeSame(403);
        }
    }

    public function testCreateCopy(): void
    {
        $this->client->request('GET', '/admin/elasticsearch-roles/create?role='.GENERATED_NAME);

        if (false == $this->callManager->hasFeature('security')) {
            $this->assertResponseStatusCodeSame(403);
        } else {
            $this->assertResponseStatusCodeSame(200);
            $this->assertPageTitleSame('Roles - Create role');
            $this->assertSelectorTextSame('h1', 'Roles');
            $this->assertSelectorTextSame('h3', 'Create role');
        }
    }

    public function testRead404(): void
    {
        $this->client->request('GET', '/admin/elasticsearch-roles/'.uniqid());

        if (false == $this->callManager->hasFeature('security')) {
            $this->assertResponseStatusCodeSame(403);
        } else {
            $this->assertResponseStatusCodeSame(404);
        }
    }

    public function testRead(): void
    {
        $this->client->request('GET', '/admin/elasticsearch-roles/'.GENERATED_NAME);

        if (false == $this->callManager->hasFeature('security')) {
            $this->assertResponseStatusCodeSame(403);
        } else {
            $this->assertResponseStatusCodeSame(200);
            $this->assertPageTitleSame('Roles - '.GENERATED_NAME);
            $this->assertSelectorTextSame('h1', 'Roles');
            $this->assertSelectorTextSame('h2', GENERATED_NAME);
            $this->assertSelectorTextSame('h3', 'Summary');
        }
    }

    public function testUpdate404(): void
    {
        $this->client->request('GET', '/admin/elasticsearch-roles/'.uniqid().'/update');

        if (false == $this->callManager->hasFeature('security')) {
            $this->assertResponseStatusCodeSame(403);
        } else {
            $this->assertResponseStatusCodeSame(404);
        }
    }

    public function testUpdate403(): void
    {
        $this->client->request('GET', '/admin/elasticsearch-roles/superuser/update');

        if (false == $this->callManager->hasFeature('security')) {
            $this->assertResponseStatusCodeSame(403);
        } else {
            $this->assertResponseStatusCodeSame(403);
        }
    }

    public function testUpdate(): void
    {
        $this->client->request('GET', '/admin/elasticsearch-roles/'.GENERATED_NAME.'/update');

        if (false == $this->callManager->hasFeature('security')) {
            $this->assertResponseStatusCodeSame(403);
        } else {
            $this->assertResponseStatusCodeSame(200);
            $this->assertPageTitleSame('Roles - '.GENERATED_NAME.' - Update');
            $this->assertSelectorTextSame('h1', 'Roles');
            $this->assertSelectorTextSame('h2', GENERATED_NAME);
            $this->assertSelectorTextSame('h3', 'Update');
        }
    }

    public function testDelete404(): void
    {
        $this->client->request('GET', '/admin/elasticsearch-roles/'.uniqid().'/delete');

        if (false == $this->callManager->hasFeature('security')) {
            $this->assertResponseStatusCodeSame(403);
        } else {
            $this->assertResponseStatusCodeSame(404);
        }
    }

    public function testDelete403(): void
    {
        $this->client->request('GET', '/admin/elasticsearch-roles/superuser/delete');

        if (false == $this->callManager->hasFeature('security')) {
            $this->assertResponseStatusCodeSame(403);
        } else {
            $this->assertResponseStatusCodeSame(403);
        }
    }

    public function testDelete(): void
    {
        $this->client->request('GET', '/admin/elasticsearch-roles/'.GENERATED_NAME.'/delete');

        if (false == $this->callManager->hasFeature('security')) {
            $this->assertResponseStatusCodeSame(403);
        } else {
            $this->assertResponseStatusCodeSame(302);
        }
    }
}
