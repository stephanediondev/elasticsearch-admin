<?php

namespace App\Tests\Controller;

/**
 * @Route("/admin")
 */
class ElasticsearchRoleControllerTest extends AbstractAppControllerTest
{
    /**
     * @Route("/elasticsearch-roles", name="elasticsearch_roles")
     */
    public function testIndex()
    {
        $this->client->request('GET', '/admin/elasticsearch-roles');

        if (false == $this->callManager->hasFeature('security')) {
            $this->assertResponseStatusCodeSame(403);
        } else {
            $this->assertResponseStatusCodeSame(200);
            $this->assertPageTitleSame('Roles');
            $this->assertSelectorTextSame('h1', 'Roles');
        }
    }

    /**
     * @Route("/elasticsearch-roles/create", name="elasticsearch_roles_create")
     */
    public function testCreate()
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

    public function testCreateCopy404()
    {
        $this->client->request('GET', '/admin/elasticsearch-roles/create?role='.uniqid());

        if (false == $this->callManager->hasFeature('security')) {
            $this->assertResponseStatusCodeSame(403);
        } else {
            $this->assertResponseStatusCodeSame(404);
        }
    }

    public function testCreateCopy403()
    {
        $this->client->request('GET', '/admin/elasticsearch-roles/create?role=superuser');

        if (false == $this->callManager->hasFeature('security')) {
            $this->assertResponseStatusCodeSame(403);
        } else {
            $this->assertResponseStatusCodeSame(403);
        }
    }

    public function testCreateCopy()
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

    /**
     * @Route("/elasticsearch-roles/{role}", name="elasticsearch_roles_read")
     */
    public function testRead404()
    {
        $this->client->request('GET', '/admin/elasticsearch-roles/'.uniqid());

        if (false == $this->callManager->hasFeature('security')) {
            $this->assertResponseStatusCodeSame(403);
        } else {
            $this->assertResponseStatusCodeSame(404);
        }
    }

    public function testRead()
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

    /**
     * @Route("/elasticsearch-roles/{role}/update", name="elasticsearch_roles_update")
     */
    public function testUpdate404()
    {
        $this->client->request('GET', '/admin/elasticsearch-roles/'.uniqid().'/update');

        if (false == $this->callManager->hasFeature('security')) {
            $this->assertResponseStatusCodeSame(403);
        } else {
            $this->assertResponseStatusCodeSame(404);
        }
    }

    public function testUpdate403()
    {
        $this->client->request('GET', '/admin/elasticsearch-roles/superuser/update');

        if (false == $this->callManager->hasFeature('security')) {
            $this->assertResponseStatusCodeSame(403);
        } else {
            $this->assertResponseStatusCodeSame(403);
        }
    }

    public function testUpdate()
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

    /**
     * @Route("/elasticsearch-roles/{role}/delete", name="elasticsearch_roles_delete")
     */
    public function testDelete404()
    {
        $this->client->request('GET', '/admin/elasticsearch-roles/'.uniqid().'/delete');

        if (false == $this->callManager->hasFeature('security')) {
            $this->assertResponseStatusCodeSame(403);
        } else {
            $this->assertResponseStatusCodeSame(404);
        }
    }

    public function testDelete403()
    {
        $this->client->request('GET', '/admin/elasticsearch-roles/superuser/delete');

        if (false == $this->callManager->hasFeature('security')) {
            $this->assertResponseStatusCodeSame(403);
        } else {
            $this->assertResponseStatusCodeSame(403);
        }
    }

    public function testDelete()
    {
        $this->client->request('GET', '/admin/elasticsearch-roles/'.GENERATED_NAME.'/delete');

        if (false == $this->callManager->hasFeature('security')) {
            $this->assertResponseStatusCodeSame(403);
        } else {
            $this->assertResponseStatusCodeSame(302);
        }
    }
}
