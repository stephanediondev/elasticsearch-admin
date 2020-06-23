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
        $this->client->request('GET', '/admin/role/'.uniqid());

        $this->assertResponseStatusCodeSame(404);
    }

    /**
     * @Route("/roles/{role}/update", name="roles_update")
     */
    public function testUpdate404()
    {
        $this->client->request('GET', '/admin/roles/'.uniqid().'/update');

        $this->assertResponseStatusCodeSame(404);
    }
}
