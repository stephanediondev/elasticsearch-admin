<?php

namespace App\Tests\Controller;

/**
 * @Route("/admin")
 */
class RoleControllerTest extends AbstractAppControllerTest
{
    /**
     * @Route("/roles", name="roles")
     */
    public function testIndex()
    {
        $this->client->request('GET', '/admin/roles');

        if (true == $this->hasFeature('security')) {
            $this->assertResponseStatusCodeSame(200);
            $this->assertPageTitleSame('Roles');
        } else {
            $this->assertResponseStatusCodeSame(403);
        }
    }

    /**
     * @Route("/roles/create", name="roles_create")
     */
    public function testCreate()
    {
        $this->client->request('GET', '/admin/roles/create');

        if (true == $this->hasFeature('security')) {
            $this->assertResponseStatusCodeSame(200);
            $this->assertPageTitleSame('Roles - Create role');
        } else {
            $this->assertResponseStatusCodeSame(403);
        }
    }

    public function testCreateCopy404()
    {
        $this->client->request('GET', '/admin/roles/create?role='.uniqid());

        $this->assertResponseStatusCodeSame(404);
    }

    /**
     * @Route("/roles/{role}", name="roles_read")
     */
    public function testRead404()
    {
        $this->client->request('GET', '/admin/roles/'.uniqid());

        if (true == $this->hasFeature('security')) {
            $this->assertResponseStatusCodeSame(404);
        } else {
            $this->assertResponseStatusCodeSame(403);
        }
    }

    public function testRead()
    {
        $this->client->request('GET', '/admin/roles/elasticsearch-admin-test');

        if (true == $this->hasFeature('security')) {
            $this->assertResponseStatusCodeSame(200);
            $this->assertPageTitleSame('Roles - elasticsearch-admin-test');
        } else {
            $this->assertResponseStatusCodeSame(403);
        }
    }

    /**
     * @Route("/roles/{role}/update", name="roles_update")
     */
    public function testUpdate404()
    {
        $this->client->request('GET', '/admin/roles/'.uniqid().'/update');

        if (true == $this->hasFeature('security')) {
            $this->assertResponseStatusCodeSame(404);
        } else {
            $this->assertResponseStatusCodeSame(403);
        }
    }

    public function testUpdate403()
    {
        $this->client->request('GET', '/admin/roles/superuser/update');

        if (true == $this->hasFeature('security')) {
            $this->assertResponseStatusCodeSame(403);
        } else {
            $this->assertResponseStatusCodeSame(403);
        }
    }

    public function testUpdate()
    {
        $this->client->request('GET', '/admin/roles/elasticsearch-admin-test/update');

        if (true == $this->hasFeature('security')) {
            $this->assertResponseStatusCodeSame(200);
            $this->assertPageTitleSame('Roles - elasticsearch-admin-test - Update');
        } else {
            $this->assertResponseStatusCodeSame(403);
        }
    }

    /**
     * @Route("/roles/{role}/delete", name="roles_delete")
     */
    public function testDelete404()
    {
        $this->client->request('GET', '/admin/roles/'.uniqid().'/delete');

        if (true == $this->hasFeature('security')) {
            $this->assertResponseStatusCodeSame(404);
        } else {
            $this->assertResponseStatusCodeSame(403);
        }
    }

    public function testDelete403()
    {
        $this->client->request('GET', '/admin/roles/superuser/delete');

        if (true == $this->hasFeature('security')) {
            $this->assertResponseStatusCodeSame(403);
        } else {
            $this->assertResponseStatusCodeSame(403);
        }
    }

    public function testDelete()
    {
        $this->client->request('GET', '/admin/roles/elasticsearch-admin-test/delete');

        if (true == $this->hasFeature('security')) {
            $this->assertResponseStatusCodeSame(302);
        } else {
            $this->assertResponseStatusCodeSame(403);
        }
    }
}
