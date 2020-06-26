<?php

namespace App\Tests\Controller;

/**
 * @Route("/admin")
 */
class UserControllerTest extends AbstractAppControllerTest
{
    /**
     * @Route("/users", name="users")
     */
    public function testIndex()
    {
        $this->client->request('GET', '/admin/users');

        $this->assertResponseStatusCodeSame(200);
        $this->assertPageTitleSame('Users');
    }

    /**
     * @Route("/users/create", name="users_create")
     */
    public function testCreate()
    {
        $this->client->request('GET', '/admin/users/create');

        $this->assertResponseStatusCodeSame(200);
        $this->assertPageTitleSame('Users - Create user');
    }

    /**
     * @Route("/users/{user}", name="users_read")
     */
    public function testRead404()
    {
        $this->client->request('GET', '/admin/users/'.uniqid());

        $this->assertResponseStatusCodeSame(404);
    }

    public function testRead()
    {
        $this->client->request('GET', '/admin/users/elasticsearch-admin-test');

        $this->assertResponseStatusCodeSame(200);
        $this->assertPageTitleSame('Users - elasticsearch-admin-test');
    }

    /**
     * @Route("/users/{user}/update", name="users_update")
     */
    public function testUpdate404()
    {
        $this->client->request('GET', '/admin/users/'.uniqid().'/update');

        $this->assertResponseStatusCodeSame(404);
    }

    public function testUpdate403()
    {
        $this->client->request('GET', '/admin/users/elastic/update');

        $this->assertResponseStatusCodeSame(403);
    }

    public function testUpdate()
    {
        $this->client->request('GET', '/admin/users/elasticsearch-admin-test/update');

        $this->assertResponseStatusCodeSame(200);
        $this->assertPageTitleSame('Users - elasticsearch-admin-test - Update');
    }

    /**
     * @Route("/users/{user}/delete", name="users_delete")
     */
    public function testDelete404()
    {
        $this->client->request('GET', '/admin/users/'.uniqid().'/delete');

        $this->assertResponseStatusCodeSame(404);
    }

    public function testDelete403()
    {
        $this->client->request('GET', '/admin/users/elastic/delete');

        $this->assertResponseStatusCodeSame(403);
    }

    public function testDelete()
    {
        $this->client->request('GET', '/admin/users/elasticsearch-admin-test/delete');

        $this->assertResponseStatusCodeSame(302);
    }
}
