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
        $this->assertSelectorTextSame('h1', 'Users');
        $this->assertSelectorTextContains('h3', 'List');
    }

    /**
     * @Route("/app-users/create", name="app_users_create")
     */
    public function testCreate()
    {
        $this->client->request('GET', '/admin/app-users/create');

        $this->assertResponseStatusCodeSame(200);
        $this->assertPageTitleSame('Users - Create user');
        $this->assertSelectorTextSame('h1', 'Users');
        $this->assertSelectorTextSame('h3', 'Create user');
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
        $this->assertSelectorTextSame('h1', 'Users');
        $this->assertSelectorTextSame('h2', 'app-admin-test');
        $this->assertSelectorTextSame('h3', 'Summary');
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
        $this->assertSelectorTextSame('h1', 'Users');
        $this->assertSelectorTextSame('h2', 'app-admin-test');
        $this->assertSelectorTextSame('h3', 'Update');
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
