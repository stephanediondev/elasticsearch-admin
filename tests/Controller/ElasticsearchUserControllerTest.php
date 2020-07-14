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

        if (true == $this->callManager->hasFeature('security')) {
            $this->assertResponseStatusCodeSame(200);
            $this->assertPageTitleSame('Elasticsearch users');
        } else {
            $this->assertResponseStatusCodeSame(403);
        }
    }

    /**
     * @Route("/elasticsearch-users/create", name="elasticsearch_users_create")
     */
    public function testCreate()
    {
        $this->client->request('GET', '/admin/elasticsearch-users/create');

        if (true == $this->callManager->hasFeature('security')) {
            $this->assertResponseStatusCodeSame(200);
            $this->assertPageTitleSame('Elasticsearch users - Create user');
        } else {
            $this->assertResponseStatusCodeSame(403);
        }
    }

    /**
     * @Route("/elasticsearch-users/{user}", name="elasticsearch_users_read")
     */
    public function testRead404()
    {
        $this->client->request('GET', '/admin/elasticsearch-users/'.uniqid());

        if (true == $this->callManager->hasFeature('security')) {
            $this->assertResponseStatusCodeSame(404);
        } else {
            $this->assertResponseStatusCodeSame(403);
        }
    }

    public function testRead()
    {
        $this->client->request('GET', '/admin/elasticsearch-users/elasticsearch-admin-test');

        if (true == $this->callManager->hasFeature('security')) {
            $this->assertResponseStatusCodeSame(200);
            $this->assertPageTitleSame('Elasticsearch users - elasticsearch-admin-test');
        } else {
            $this->assertResponseStatusCodeSame(403);
        }
    }

    /**
     * @Route("/elasticsearch-users/{user}/update", name="elasticsearch_users_update")
     */
    public function testUpdate404()
    {
        $this->client->request('GET', '/admin/elasticsearch-users/'.uniqid().'/update');

        if (true == $this->callManager->hasFeature('security')) {
            $this->assertResponseStatusCodeSame(404);
        } else {
            $this->assertResponseStatusCodeSame(403);
        }
    }

    public function testUpdate403()
    {
        $this->client->request('GET', '/admin/elasticsearch-users/elastic/update');

        if (true == $this->callManager->hasFeature('security')) {
            $this->assertResponseStatusCodeSame(403);
        } else {
            $this->assertResponseStatusCodeSame(403);
        }
    }

    public function testUpdate()
    {
        $this->client->request('GET', '/admin/elasticsearch-users/elasticsearch-admin-test/update');

        if (true == $this->callManager->hasFeature('security')) {
            $this->assertResponseStatusCodeSame(200);
            $this->assertPageTitleSame('Elasticsearch users - elasticsearch-admin-test - Update');
        } else {
            $this->assertResponseStatusCodeSame(403);
        }
    }

    /**
     * @Route("/elasticsearch-users/{user}/disable", name="elasticsearch_users_disable")
     */
    public function testDisable404()
    {
        $this->client->request('GET', '/admin/elasticsearch-users/'.uniqid().'/disable');

        if (true == $this->callManager->hasFeature('security')) {
            $this->assertResponseStatusCodeSame(404);
        } else {
            $this->assertResponseStatusCodeSame(403);
        }
    }

    public function testDisable403()
    {
        $this->client->request('GET', '/admin/elasticsearch-users/elastic/disable');

        if (true == $this->callManager->hasFeature('security')) {
            $this->assertResponseStatusCodeSame(403);
        } else {
            $this->assertResponseStatusCodeSame(403);
        }
    }

    public function testDisable()
    {
        $this->client->request('GET', '/admin/elasticsearch-users/elasticsearch-admin-test/disable');

        if (true == $this->callManager->hasFeature('security')) {
            $this->assertResponseStatusCodeSame(302);
        } else {
            $this->assertResponseStatusCodeSame(403);
        }
    }

    /**
     * @Route("/elasticsearch-users/{user}/enable", name="elasticsearch_users_enable")
     */
    public function testEnable404()
    {
        $this->client->request('GET', '/admin/elasticsearch-users/'.uniqid().'/enable');

        if (true == $this->callManager->hasFeature('security')) {
            $this->assertResponseStatusCodeSame(404);
        } else {
            $this->assertResponseStatusCodeSame(403);
        }
    }

    public function testEnable403()
    {
        $this->client->request('GET', '/admin/elasticsearch-users/elastic/enable');

        if (true == $this->callManager->hasFeature('security')) {
            $this->assertResponseStatusCodeSame(403);
        } else {
            $this->assertResponseStatusCodeSame(403);
        }
    }

    public function testEnable()
    {
        $this->client->request('GET', '/admin/elasticsearch-users/elasticsearch-admin-test/enable');

        if (true == $this->callManager->hasFeature('security')) {
            $this->assertResponseStatusCodeSame(302);
        } else {
            $this->assertResponseStatusCodeSame(403);
        }
    }

    /**
     * @Route("/elasticsearch-users/{user}/delete", name="elasticsearch_users_delete")
     */
    public function testDelete404()
    {
        $this->client->request('GET', '/admin/elasticsearch-users/'.uniqid().'/delete');

        if (true == $this->callManager->hasFeature('security')) {
            $this->assertResponseStatusCodeSame(404);
        } else {
            $this->assertResponseStatusCodeSame(403);
        }
    }

    public function testDelete403()
    {
        $this->client->request('GET', '/admin/elasticsearch-users/elastic/delete');

        if (true == $this->callManager->hasFeature('security')) {
            $this->assertResponseStatusCodeSame(403);
        } else {
            $this->assertResponseStatusCodeSame(403);
        }
    }

    public function testDelete()
    {
        $this->client->request('GET', '/admin/elasticsearch-users/elasticsearch-admin-test/delete');

        if (true == $this->callManager->hasFeature('security')) {
            $this->assertResponseStatusCodeSame(302);
        } else {
            $this->assertResponseStatusCodeSame(403);
        }
    }
}
