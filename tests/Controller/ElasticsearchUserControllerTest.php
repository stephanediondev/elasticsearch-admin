<?php

namespace App\Tests\Controller;

/**
 * @Route("/admin")
 */
class ElasticsearchUserControllerTest extends AbstractAppControllerTest
{
    /**
     * @Route("/elasticsearch-users", name="elasticsearch_users")
     */
    public function testIndex()
    {
        $this->client->request('GET', '/admin/elasticsearch-users');

        if (false == $this->callManager->hasFeature('security')) {
            $this->assertResponseStatusCodeSame(403);
        } else {
            $this->assertResponseStatusCodeSame(200);
            $this->assertPageTitleSame('Users');
        }
    }

    /**
     * @Route("/elasticsearch-users/create", name="elasticsearch_users_create")
     */
    public function testCreate()
    {
        $this->client->request('GET', '/admin/elasticsearch-users/create');

        if (false == $this->callManager->hasFeature('security')) {
            $this->assertResponseStatusCodeSame(403);
        } else {
            $this->assertResponseStatusCodeSame(200);
            $this->assertPageTitleSame('Users - Create user');
        }
    }

    /**
     * @Route("/elasticsearch-users/{user}", name="elasticsearch_users_read")
     */
    public function testRead404()
    {
        $this->client->request('GET', '/admin/elasticsearch-users/'.uniqid());

        if (false == $this->callManager->hasFeature('security')) {
            $this->assertResponseStatusCodeSame(403);
        } else {
            $this->assertResponseStatusCodeSame(404);
        }
    }

    public function testRead()
    {
        $this->client->request('GET', '/admin/elasticsearch-users/elasticsearch-admin-test');

        if (false == $this->callManager->hasFeature('security')) {
            $this->assertResponseStatusCodeSame(403);
        } else {
            $this->assertResponseStatusCodeSame(200);
            $this->assertPageTitleSame('Users - elasticsearch-admin-test');
        }
    }

    /**
     * @Route("/elasticsearch-users/{user}/update", name="elasticsearch_users_update")
     */
    public function testUpdate404()
    {
        $this->client->request('GET', '/admin/elasticsearch-users/'.uniqid().'/update');

        if (false == $this->callManager->hasFeature('security')) {
            $this->assertResponseStatusCodeSame(403);
        } else {
            $this->assertResponseStatusCodeSame(404);
        }
    }

    public function testUpdate403()
    {
        $this->client->request('GET', '/admin/elasticsearch-users/elastic/update');

        if (false == $this->callManager->hasFeature('security')) {
            $this->assertResponseStatusCodeSame(403);
        } else {
            $this->assertResponseStatusCodeSame(403);
        }
    }

    public function testUpdate()
    {
        $this->client->request('GET', '/admin/elasticsearch-users/elasticsearch-admin-test/update');

        if (false == $this->callManager->hasFeature('security')) {
            $this->assertResponseStatusCodeSame(403);
        } else {
            $this->assertResponseStatusCodeSame(200);
            $this->assertPageTitleSame('Users - elasticsearch-admin-test - Update');
        }
    }

    /**
     * @Route("/elasticsearch-users/{user}/disable", name="elasticsearch_users_disable")
     */
    public function testDisable404()
    {
        $this->client->request('GET', '/admin/elasticsearch-users/'.uniqid().'/disable');

        if (false == $this->callManager->hasFeature('security')) {
            $this->assertResponseStatusCodeSame(403);
        } else {
            $this->assertResponseStatusCodeSame(404);
        }
    }

    public function testDisable403()
    {
        $this->client->request('GET', '/admin/elasticsearch-users/elastic/disable');

        if (false == $this->callManager->hasFeature('security')) {
            $this->assertResponseStatusCodeSame(403);
        } else {
            $this->assertResponseStatusCodeSame(403);
        }
    }

    public function testDisable()
    {
        $this->client->request('GET', '/admin/elasticsearch-users/elasticsearch-admin-test/disable');

        if (false == $this->callManager->hasFeature('security')) {
            $this->assertResponseStatusCodeSame(403);
        } else {
            $this->assertResponseStatusCodeSame(302);
        }
    }

    /**
     * @Route("/elasticsearch-users/{user}/enable", name="elasticsearch_users_enable")
     */
    public function testEnable404()
    {
        $this->client->request('GET', '/admin/elasticsearch-users/'.uniqid().'/enable');

        if (false == $this->callManager->hasFeature('security')) {
            $this->assertResponseStatusCodeSame(403);
        } else {
            $this->assertResponseStatusCodeSame(404);
        }
    }

    public function testEnable403()
    {
        $this->client->request('GET', '/admin/elasticsearch-users/elastic/enable');

        if (false == $this->callManager->hasFeature('security')) {
            $this->assertResponseStatusCodeSame(403);
        } else {
            $this->assertResponseStatusCodeSame(403);
        }
    }

    public function testEnable()
    {
        $this->client->request('GET', '/admin/elasticsearch-users/elasticsearch-admin-test/enable');

        if (false == $this->callManager->hasFeature('security')) {
            $this->assertResponseStatusCodeSame(403);
        } else {
            $this->assertResponseStatusCodeSame(302);
        }
    }

    /**
     * @Route("/elasticsearch-users/{user}/delete", name="elasticsearch_users_delete")
     */
    public function testDelete404()
    {
        $this->client->request('GET', '/admin/elasticsearch-users/'.uniqid().'/delete');

        if (false == $this->callManager->hasFeature('security')) {
            $this->assertResponseStatusCodeSame(403);
        } else {
            $this->assertResponseStatusCodeSame(404);
        }
    }

    public function testDelete403()
    {
        $this->client->request('GET', '/admin/elasticsearch-users/elastic/delete');

        if (false == $this->callManager->hasFeature('security')) {
            $this->assertResponseStatusCodeSame(403);
        } else {
            $this->assertResponseStatusCodeSame(403);
        }
    }

    public function testDelete()
    {
        $this->client->request('GET', '/admin/elasticsearch-users/elasticsearch-admin-test/delete');

        if (false == $this->callManager->hasFeature('security')) {
            $this->assertResponseStatusCodeSame(403);
        } else {
            $this->assertResponseStatusCodeSame(302);
        }
    }
}
