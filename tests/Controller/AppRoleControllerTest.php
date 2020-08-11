<?php

namespace App\Tests\Controller;

/**
 * @Route("/admin")
 */
class AppRoleControllerTest extends AbstractAppControllerTest
{
    /**
     * @Route("/app-roles", name="app_roles")
     */
    public function testIndex()
    {
        $this->client->request('GET', '/admin/app-roles');

        $this->assertResponseStatusCodeSame(200);
        $this->assertPageTitleSame('Roles');
    }

    /**
     * @Route("/app-roles/create", name="app_roles_create")
     */
    public function testCreate()
    {
        $this->client->request('GET', '/admin/app-roles/create');

        $this->assertResponseStatusCodeSame(200);
        $this->assertPageTitleSame('Roles - Create role');
    }

    /**
     * @Route("/app-roles/{role}", name="app_roles_read")
     */
    public function testRead404()
    {
        $this->client->request('GET', '/admin/app-roles/'.uniqid());

        $this->assertResponseStatusCodeSame(404);
    }

    public function testRead()
    {
        $this->client->request('GET', '/admin/app-roles/app-admin-test');

        $this->assertResponseStatusCodeSame(200);
        $this->assertPageTitleSame('Roles - app-admin-test');
    }

    /**
     * @Route("/app-roles/{role}/update", name="app_roles_update")
     */
    public function testUpdate404()
    {
        $this->client->request('GET', '/admin/app-roles/'.uniqid().'/update');

        $this->assertResponseStatusCodeSame(404);
    }

    public function testUpdate()
    {
        $this->client->request('GET', '/admin/app-roles/app-admin-test/update');

        $this->assertResponseStatusCodeSame(200);
        $this->assertPageTitleSame('Roles - app-admin-test - Update');
    }

    /**
     * @Route("/app-roles/{role}/delete", name="app_roles_delete")
     */
    public function testDelete404()
    {
        $this->client->request('GET', '/admin/app-roles/'.uniqid().'/delete');

        $this->assertResponseStatusCodeSame(404);
    }

    public function testDelete()
    {
        $this->client->request('GET', '/admin/app-roles/app-admin-test/delete');

        $this->assertResponseStatusCodeSame(302);
    }
}
