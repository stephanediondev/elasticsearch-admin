<?php

namespace App\Tests\Controller;

/**
 * @Route("/admin")
 */
class AppUserControllerTest extends AbstractAppControllerTest
{
    /**
     * @Route("/app-users", name="app_users")
     */
    public function testIndex()
    {
        $this->client->request('GET', '/admin/app-users');

        $this->assertResponseStatusCodeSame(200);
        $this->assertPageTitleSame('Users');
    }

    /**
     * @Route("/app-users/create", name="app_users_create")
     */
    public function testCreate()
    {
        $this->client->request('GET', '/admin/app-users/create');

        $this->assertResponseStatusCodeSame(200);
        $this->assertPageTitleSame('Users - Create user');
    }

    /**
     * @Route("/app-users/{user}", name="app_users_read")
     */
    public function testRead404()
    {
        $this->client->request('GET', '/admin/app-users/'.uniqid());

        $this->assertResponseStatusCodeSame(404);
    }

    public function testRead()
    {
        $this->client->request('GET', '/admin/app-users/app-admin-test');

        $this->assertResponseStatusCodeSame(200);
        $this->assertPageTitleSame('Users - app-admin-test');
    }

    /**
     * @Route("/app-users/{user}/update", name="app_users_update")
     */
    public function testUpdate404()
    {
        $this->client->request('GET', '/admin/app-users/'.uniqid().'/update');

        $this->assertResponseStatusCodeSame(404);
    }

    public function testUpdate()
    {
        $this->client->request('GET', '/admin/app-users/app-admin-test/update');

        $this->assertResponseStatusCodeSame(200);
        $this->assertPageTitleSame('Users - app-admin-test - Update');
    }

    /**
     * @Route("/app-users/{user}/delete", name="app_users_delete")
     */
    public function testDelete404()
    {
        $this->client->request('GET', '/admin/app-users/'.uniqid().'/delete');

        $this->assertResponseStatusCodeSame(404);
    }

    public function testDelete()
    {
        $this->client->request('GET', '/admin/app-users/app-admin-test/delete');

        $this->assertResponseStatusCodeSame(302);
    }
}
