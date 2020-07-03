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

        if (true == isset($this->xpack['features']['security']) && true == $this->xpack['features']['security']['enabled']) {
            $this->assertResponseStatusCodeSame(200);
            $this->assertPageTitleSame('Users');
        } else {
            $this->assertResponseStatusCodeSame(403);
        }
    }

    /**
     * @Route("/users/create", name="users_create")
     */
    public function testCreate()
    {
        $this->client->request('GET', '/admin/users/create');

        if (true == isset($this->xpack['features']['security']) && true == $this->xpack['features']['security']['enabled']) {
            $this->assertResponseStatusCodeSame(200);
            $this->assertPageTitleSame('Users - Create user');
        } else {
            $this->assertResponseStatusCodeSame(403);
        }
    }

    /**
     * @Route("/users/{user}", name="users_read")
     */
    public function testRead404()
    {
        $this->client->request('GET', '/admin/users/'.uniqid());

        if (true == isset($this->xpack['features']['security']) && true == $this->xpack['features']['security']['enabled']) {
            $this->assertResponseStatusCodeSame(404);
        } else {
            $this->assertResponseStatusCodeSame(403);
        }
    }

    public function testRead()
    {
        $this->client->request('GET', '/admin/users/elasticsearch-admin-test');

        if (true == isset($this->xpack['features']['security']) && true == $this->xpack['features']['security']['enabled']) {
            $this->assertResponseStatusCodeSame(200);
            $this->assertPageTitleSame('Users - elasticsearch-admin-test');
        } else {
            $this->assertResponseStatusCodeSame(403);
        }
    }

    /**
     * @Route("/users/{user}/update", name="users_update")
     */
    public function testUpdate404()
    {
        $this->client->request('GET', '/admin/users/'.uniqid().'/update');

        if (true == isset($this->xpack['features']['security']) && true == $this->xpack['features']['security']['enabled']) {
            $this->assertResponseStatusCodeSame(404);
        } else {
            $this->assertResponseStatusCodeSame(500);
        }
    }

    public function testUpdate403()
    {
        $this->client->request('GET', '/admin/users/elastic/update');

        if (true == isset($this->xpack['features']['security']) && true == $this->xpack['features']['security']['enabled']) {
            $this->assertResponseStatusCodeSame(403);
        } else {
            $this->assertResponseStatusCodeSame(403);
        }
    }

    public function testUpdate()
    {
        $this->client->request('GET', '/admin/users/elasticsearch-admin-test/update');

        if (true == isset($this->xpack['features']['security']) && true == $this->xpack['features']['security']['enabled']) {
            $this->assertResponseStatusCodeSame(200);
            $this->assertPageTitleSame('Users - elasticsearch-admin-test - Update');
        } else {
            $this->assertResponseStatusCodeSame(403);
        }
    }

    /**
     * @Route("/users/{user}/disable", name="users_disable")
     */
    public function testDisable404()
    {
        $this->client->request('GET', '/admin/users/'.uniqid().'/disable');

        if (true == isset($this->xpack['features']['security']) && true == $this->xpack['features']['security']['enabled']) {
            $this->assertResponseStatusCodeSame(404);
        } else {
            $this->assertResponseStatusCodeSame(403);
        }
    }

    public function testDisable403()
    {
        $this->client->request('GET', '/admin/users/elastic/disable');

        if (true == isset($this->xpack['features']['security']) && true == $this->xpack['features']['security']['enabled']) {
            $this->assertResponseStatusCodeSame(403);
        } else {
            $this->assertResponseStatusCodeSame(403);
        }
    }

    public function testDisable()
    {
        $this->client->request('GET', '/admin/users/elasticsearch-admin-test/disable');

        if (true == isset($this->xpack['features']['security']) && true == $this->xpack['features']['security']['enabled']) {
            $this->assertResponseStatusCodeSame(302);
        } else {
            $this->assertResponseStatusCodeSame(403);
        }
    }

    /**
     * @Route("/users/{user}/enable", name="users_enable")
     */
    public function testEnable404()
    {
        $this->client->request('GET', '/admin/users/'.uniqid().'/enable');

        if (true == isset($this->xpack['features']['security']) && true == $this->xpack['features']['security']['enabled']) {
            $this->assertResponseStatusCodeSame(404);
        } else {
            $this->assertResponseStatusCodeSame(403);
        }
    }

    public function testEnable403()
    {
        $this->client->request('GET', '/admin/users/elastic/enable');

        if (true == isset($this->xpack['features']['security']) && true == $this->xpack['features']['security']['enabled']) {
            $this->assertResponseStatusCodeSame(403);
        } else {
            $this->assertResponseStatusCodeSame(403);
        }
    }

    public function testEnable()
    {
        $this->client->request('GET', '/admin/users/elasticsearch-admin-test/enable');

        if (true == isset($this->xpack['features']['security']) && true == $this->xpack['features']['security']['enabled']) {
            $this->assertResponseStatusCodeSame(302);
        } else {
            $this->assertResponseStatusCodeSame(403);
        }
    }

    /**
     * @Route("/users/{user}/delete", name="users_delete")
     */
    public function testDelete404()
    {
        $this->client->request('GET', '/admin/users/'.uniqid().'/delete');

        if (true == isset($this->xpack['features']['security']) && true == $this->xpack['features']['security']['enabled']) {
            $this->assertResponseStatusCodeSame(404);
        } else {
            $this->assertResponseStatusCodeSame(403);
        }
    }

    public function testDelete403()
    {
        $this->client->request('GET', '/admin/users/elastic/delete');

        if (true == isset($this->xpack['features']['security']) && true == $this->xpack['features']['security']['enabled']) {
            $this->assertResponseStatusCodeSame(403);
        } else {
            $this->assertResponseStatusCodeSame(403);
        }
    }

    public function testDelete()
    {
        $this->client->request('GET', '/admin/users/elasticsearch-admin-test/delete');

        if (true == isset($this->xpack['features']['security']) && true == $this->xpack['features']['security']['enabled']) {
            $this->assertResponseStatusCodeSame(302);
        } else {
            $this->assertResponseStatusCodeSame(403);
        }
    }
}
