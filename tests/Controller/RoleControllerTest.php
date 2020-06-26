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

        $this->assertResponseStatusCodeSame(200);
        $this->assertPageTitleSame('Roles');
    }

    /**
     * @Route("/roles/create", name="roles_create")
     */
    public function testCreate()
    {
        $this->client->request('GET', '/admin/roles/create');

        $this->assertResponseStatusCodeSame(200);
        $this->assertPageTitleSame('Roles - Create role');
    }

    /**
     * @Route("/roles/{role}", name="roles_read")
     */
    public function testRead404()
    {
        $this->client->request('GET', '/admin/roles/'.uniqid());

        $this->assertResponseStatusCodeSame(404);
    }

    public function testRead()
    {
        $this->client->request('GET', '/admin/roles/elasticsearch-admin-test');

        $this->assertResponseStatusCodeSame(200);
        $this->assertPageTitleSame('Roles - elasticsearch-admin-test');
    }

    /**
     * @Route("/roles/{role}/update", name="roles_update")
     */
    public function testUpdate404()
    {
        $this->client->request('GET', '/admin/roles/'.uniqid().'/update');

        $this->assertResponseStatusCodeSame(404);
    }

    public function testUpdate403()
    {
        $this->client->request('GET', '/admin/roles/superuser/update');

        $this->assertResponseStatusCodeSame(403);
    }

    public function testUpdate()
    {
        $this->client->request('GET', '/admin/roles/elasticsearch-admin-test/update');

        $this->assertResponseStatusCodeSame(200);
        $this->assertPageTitleSame('Roles - elasticsearch-admin-test - Update');
    }

    /**
     * @Route("/roles/{role}/delete", name="roles_delete")
     */
    public function testDelete404()
    {
        $this->client->request('GET', '/admin/roles/'.uniqid().'/delete');

        $this->assertResponseStatusCodeSame(404);
    }

    public function testDelete403()
    {
        $this->client->request('GET', '/admin/roles/superuser/delete');

        $this->assertResponseStatusCodeSame(403);
    }

    public function testDelete()
    {
        $this->client->request('GET', '/admin/roles/elasticsearch-admin-test/delete');

        $this->assertResponseStatusCodeSame(302);
    }
}
