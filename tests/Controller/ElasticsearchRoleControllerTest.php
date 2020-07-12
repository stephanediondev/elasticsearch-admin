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

        if (true == $this->hasFeature('security')) {
            $this->assertResponseStatusCodeSame(200);
            $this->assertPageTitleSame('Elasticsearch roles');
        } else {
            $this->assertResponseStatusCodeSame(403);
        }
    }

    /**
     * @Route("/elasticsearch-roles/create", name="elasticsearch_roles_create")
     */
    public function testCreate()
    {
        $this->client->request('GET', '/admin/elasticsearch-roles/create');

        if (true == $this->hasFeature('security')) {
            $this->assertResponseStatusCodeSame(200);
            $this->assertPageTitleSame('Elasticsearch roles - Create role');
        } else {
            $this->assertResponseStatusCodeSame(403);
        }
    }

    public function testCreateCopy404()
    {
        $this->client->request('GET', '/admin/elasticsearch-roles/create?role='.uniqid());

        if (true == $this->hasFeature('security')) {
            $this->assertResponseStatusCodeSame(404);
        } else {
            $this->assertResponseStatusCodeSame(403);
        }
    }

    /**
     * @Route("/elasticsearch-roles/{role}", name="elasticsearch_roles_read")
     */
    public function testRead404()
    {
        $this->client->request('GET', '/admin/elasticsearch-roles/'.uniqid());

        if (true == $this->hasFeature('security')) {
            $this->assertResponseStatusCodeSame(404);
        } else {
            $this->assertResponseStatusCodeSame(403);
        }
    }

    public function testRead()
    {
        $this->client->request('GET', '/admin/elasticsearch-roles/elasticsearch-admin-test');

        if (true == $this->hasFeature('security')) {
            $this->assertResponseStatusCodeSame(200);
            $this->assertPageTitleSame('Elasticsearch roles - elasticsearch-admin-test');
        } else {
            $this->assertResponseStatusCodeSame(403);
        }
    }

    /**
     * @Route("/elasticsearch-roles/{role}/update", name="elasticsearch_roles_update")
     */
    public function testUpdate404()
    {
        $this->client->request('GET', '/admin/elasticsearch-roles/'.uniqid().'/update');

        if (true == $this->hasFeature('security')) {
            $this->assertResponseStatusCodeSame(404);
        } else {
            $this->assertResponseStatusCodeSame(403);
        }
    }

    public function testUpdate403()
    {
        $this->client->request('GET', '/admin/elasticsearch-roles/superuser/update');

        if (true == $this->hasFeature('security')) {
            $this->assertResponseStatusCodeSame(403);
        } else {
            $this->assertResponseStatusCodeSame(403);
        }
    }

    public function testUpdate()
    {
        $this->client->request('GET', '/admin/elasticsearch-roles/elasticsearch-admin-test/update');

        if (true == $this->hasFeature('security')) {
            $this->assertResponseStatusCodeSame(200);
            $this->assertPageTitleSame('Elasticsearch roles - elasticsearch-admin-test - Update');
        } else {
            $this->assertResponseStatusCodeSame(403);
        }
    }

    /**
     * @Route("/elasticsearch-roles/{role}/delete", name="elasticsearch_roles_delete")
     */
    public function testDelete404()
    {
        $this->client->request('GET', '/admin/elasticsearch-roles/'.uniqid().'/delete');

        if (true == $this->hasFeature('security')) {
            $this->assertResponseStatusCodeSame(404);
        } else {
            $this->assertResponseStatusCodeSame(403);
        }
    }

    public function testDelete403()
    {
        $this->client->request('GET', '/admin/elasticsearch-roles/superuser/delete');

        if (true == $this->hasFeature('security')) {
            $this->assertResponseStatusCodeSame(403);
        } else {
            $this->assertResponseStatusCodeSame(403);
        }
    }

    public function testDelete()
    {
        $this->client->request('GET', '/admin/elasticsearch-roles/elasticsearch-admin-test/delete');

        if (true == $this->hasFeature('security')) {
            $this->assertResponseStatusCodeSame(302);
        } else {
            $this->assertResponseStatusCodeSame(403);
        }
    }
}
