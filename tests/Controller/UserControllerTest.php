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
        $this->client->request('GET', '/admin/user/'.uniqid());

        $this->assertResponseStatusCodeSame(404);
    }

    /**
     * @Route("/users/{user}/update", name="users_update")
     */
    public function testUpdate404()
    {
        $this->client->request('GET', '/admin/user/'.uniqid().'/update');

        $this->assertResponseStatusCodeSame(404);
    }
}
