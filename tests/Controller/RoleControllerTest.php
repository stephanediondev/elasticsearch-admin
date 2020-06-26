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

        if (true == isset($this->xpack['features']['security']) && true == $this->xpack['features']['security']['enabled']) {
            $this->assertResponseStatusCodeSame(200);
            $this->assertPageTitleSame('Roles');
        } else {
            $this->assertResponseStatusCodeSame(500);
        }
    }

    /**
     * @Route("/roles/create", name="roles_create")
     */
    public function testCreate()
    {
        $this->client->request('GET', '/admin/roles/create');

        if (true == isset($this->xpack['features']['security']) && true == $this->xpack['features']['security']['enabled']) {
            $this->assertResponseStatusCodeSame(200);
            $this->assertPageTitleSame('Roles - Create role');
        } else {
            $this->assertResponseStatusCodeSame(500);
        }
    }

    /**
     * @Route("/roles/{role}", name="roles_read")
     */
    public function testRead404()
    {
        $this->client->request('GET', '/admin/roles/'.uniqid());

        if (true == isset($this->xpack['features']['security']) && true == $this->xpack['features']['security']['enabled']) {
            $this->assertResponseStatusCodeSame(404);
        } else {
            $this->assertResponseStatusCodeSame(500);
        }
    }

    public function testRead()
    {
        $this->client->request('GET', '/admin/roles/elasticsearch-admin-test');

        if (true == isset($this->xpack['features']['security']) && true == $this->xpack['features']['security']['enabled']) {
            $this->assertResponseStatusCodeSame(200);
            $this->assertPageTitleSame('Roles - elasticsearch-admin-test');
        } else {
            $this->assertResponseStatusCodeSame(500);
        }
    }

    /**
     * @Route("/roles/{role}/update", name="roles_update")
     */
    public function testUpdate404()
    {
        $this->client->request('GET', '/admin/roles/'.uniqid().'/update');

        if (true == isset($this->xpack['features']['security']) && true == $this->xpack['features']['security']['enabled']) {
            $this->assertResponseStatusCodeSame(404);
        } else {
            $this->assertResponseStatusCodeSame(500);
        }
    }

    public function testUpdate403()
    {
        $this->client->request('GET', '/admin/roles/superuser/update');

        if (true == isset($this->xpack['features']['security']) && true == $this->xpack['features']['security']['enabled']) {
            $this->assertResponseStatusCodeSame(403);
        } else {
            $this->assertResponseStatusCodeSame(500);
        }
    }

    public function testUpdate()
    {
        $this->client->request('GET', '/admin/roles/elasticsearch-admin-test/update');

        if (true == isset($this->xpack['features']['security']) && true == $this->xpack['features']['security']['enabled']) {
            $this->assertResponseStatusCodeSame(200);
            $this->assertPageTitleSame('Roles - elasticsearch-admin-test - Update');
        } else {
            $this->assertResponseStatusCodeSame(500);
        }
    }

    /**
     * @Route("/roles/{role}/delete", name="roles_delete")
     */
    public function testDelete404()
    {
        $this->client->request('GET', '/admin/roles/'.uniqid().'/delete');

        if (true == isset($this->xpack['features']['security']) && true == $this->xpack['features']['security']['enabled']) {
            $this->assertResponseStatusCodeSame(404);
        } else {
            $this->assertResponseStatusCodeSame(500);
        }
    }

    public function testDelete403()
    {
        $this->client->request('GET', '/admin/roles/superuser/delete');

        if (true == isset($this->xpack['features']['security']) && true == $this->xpack['features']['security']['enabled']) {
            $this->assertResponseStatusCodeSame(403);
        } else {
            $this->assertResponseStatusCodeSame(500);
        }
    }

    public function testDelete()
    {
        $this->client->request('GET', '/admin/roles/elasticsearch-admin-test/delete');

        if (true == isset($this->xpack['features']['security']) && true == $this->xpack['features']['security']['enabled']) {
            $this->assertResponseStatusCodeSame(302);
        } else {
            $this->assertResponseStatusCodeSame(500);
        }
    }
}
