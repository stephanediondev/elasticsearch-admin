<?php

namespace App\Tests\Controller\Part2;

use App\Tests\Controller\AbstractAppControllerTest;

/**
 * @Route("/admin")
 */
class ElasticsearchUserControllerTest extends AbstractAppControllerTest
{
    /**
     * @Route("/elasticsearch-users", name="elasticsearch_users")
     */
    public function testIndex(): void
    {
        $this->client->request('GET', '/admin/elasticsearch-users');

        if (false == $this->callManager->hasFeature('security')) {
            $this->assertResponseStatusCodeSame(403);
        } else {
            $this->assertResponseStatusCodeSame(200);
            $this->assertPageTitleSame('Users');
            $this->assertSelectorTextSame('h1', 'Users');
            $this->assertSelectorTextContains('h3', 'List');
        }
    }

    /**
     * @Route("/elasticsearch-users/create", name="elasticsearch_users_create")
     */
    public function testCreate(): void
    {
        $this->client->request('GET', '/admin/elasticsearch-users/create');

        if (false == $this->callManager->hasFeature('security')) {
            $this->assertResponseStatusCodeSame(403);
        } else {
            $this->assertResponseStatusCodeSame(200);
            $this->assertPageTitleSame('Users - Create user');
            $this->assertSelectorTextSame('h1', 'Users');
            $this->assertSelectorTextSame('h3', 'Create user');

            $values = [
                'data[name]' => GENERATED_NAME,
                'data[password]' => uniqid(),
            ];
            $this->client->submitForm('Submit', $values);

            $this->assertResponseStatusCodeSame(302);

            $this->client->followRedirect();
            $this->assertPageTitleSame('Users - '.GENERATED_NAME);
            $this->assertSelectorTextSame('h1', 'Users');
            $this->assertSelectorTextSame('h2', GENERATED_NAME);
            $this->assertSelectorTextSame('h3', 'Summary');
        }
    }

    /**
     * @Route("/elasticsearch-users/{user}", name="elasticsearch_users_read")
     */
    public function testRead404(): void
    {
        $this->client->request('GET', '/admin/elasticsearch-users/'.uniqid());

        if (false == $this->callManager->hasFeature('security')) {
            $this->assertResponseStatusCodeSame(403);
        } else {
            $this->assertResponseStatusCodeSame(404);
        }
    }

    public function testRead(): void
    {
        $this->client->request('GET', '/admin/elasticsearch-users/'.GENERATED_NAME);

        if (false == $this->callManager->hasFeature('security')) {
            $this->assertResponseStatusCodeSame(403);
        } else {
            $this->assertResponseStatusCodeSame(200);
            $this->assertPageTitleSame('Users - '.GENERATED_NAME);
            $this->assertSelectorTextSame('h1', 'Users');
            $this->assertSelectorTextSame('h2', GENERATED_NAME);
            $this->assertSelectorTextSame('h3', 'Summary');
        }
    }

    /**
     * @Route("/elasticsearch-users/{user}/update", name="elasticsearch_users_update")
     */
    public function testUpdate404(): void
    {
        $this->client->request('GET', '/admin/elasticsearch-users/'.uniqid().'/update');

        if (false == $this->callManager->hasFeature('security')) {
            $this->assertResponseStatusCodeSame(403);
        } else {
            $this->assertResponseStatusCodeSame(404);
        }
    }

    public function testUpdate403(): void
    {
        $this->client->request('GET', '/admin/elasticsearch-users/elastic/update');

        if (false == $this->callManager->hasFeature('security')) {
            $this->assertResponseStatusCodeSame(403);
        } else {
            $this->assertResponseStatusCodeSame(403);
        }
    }

    public function testUpdate(): void
    {
        $this->client->request('GET', '/admin/elasticsearch-users/'.GENERATED_NAME.'/update');

        if (false == $this->callManager->hasFeature('security')) {
            $this->assertResponseStatusCodeSame(403);
        } else {
            $this->assertResponseStatusCodeSame(200);
            $this->assertPageTitleSame('Users - '.GENERATED_NAME.' - Update');
            $this->assertSelectorTextSame('h1', 'Users');
            $this->assertSelectorTextSame('h2', GENERATED_NAME);
            $this->assertSelectorTextSame('h3', 'Update');
        }
    }

    /**
     * @Route("/elasticsearch-users/{user}/disable", name="elasticsearch_users_disable")
     */
    public function testDisable404(): void
    {
        $this->client->request('GET', '/admin/elasticsearch-users/'.uniqid().'/disable');

        if (false == $this->callManager->hasFeature('security')) {
            $this->assertResponseStatusCodeSame(403);
        } else {
            $this->assertResponseStatusCodeSame(404);
        }
    }

    public function testDisable403(): void
    {
        $this->client->request('GET', '/admin/elasticsearch-users/elastic/disable');

        if (false == $this->callManager->hasFeature('security')) {
            $this->assertResponseStatusCodeSame(403);
        } else {
            $this->assertResponseStatusCodeSame(403);
        }
    }

    public function testDisable(): void
    {
        $this->client->request('GET', '/admin/elasticsearch-users/'.GENERATED_NAME.'/disable');

        if (false == $this->callManager->hasFeature('security')) {
            $this->assertResponseStatusCodeSame(403);
        } else {
            $this->assertResponseStatusCodeSame(302);
        }
    }

    /**
     * @Route("/elasticsearch-users/{user}/enable", name="elasticsearch_users_enable")
     */
    public function testEnable404(): void
    {
        $this->client->request('GET', '/admin/elasticsearch-users/'.uniqid().'/enable');

        if (false == $this->callManager->hasFeature('security')) {
            $this->assertResponseStatusCodeSame(403);
        } else {
            $this->assertResponseStatusCodeSame(404);
        }
    }

    public function testEnable403(): void
    {
        $this->client->request('GET', '/admin/elasticsearch-users/elastic/enable');

        if (false == $this->callManager->hasFeature('security')) {
            $this->assertResponseStatusCodeSame(403);
        } else {
            $this->assertResponseStatusCodeSame(403);
        }
    }

    public function testEnable(): void
    {
        $this->client->request('GET', '/admin/elasticsearch-users/'.GENERATED_NAME.'/enable');

        if (false == $this->callManager->hasFeature('security')) {
            $this->assertResponseStatusCodeSame(403);
        } else {
            $this->assertResponseStatusCodeSame(302);
        }
    }

    /**
     * @Route("/elasticsearch-users/{user}/delete", name="elasticsearch_users_delete")
     */
    public function testDelete404(): void
    {
        $this->client->request('GET', '/admin/elasticsearch-users/'.uniqid().'/delete');

        if (false == $this->callManager->hasFeature('security')) {
            $this->assertResponseStatusCodeSame(403);
        } else {
            $this->assertResponseStatusCodeSame(404);
        }
    }

    public function testDelete403(): void
    {
        $this->client->request('GET', '/admin/elasticsearch-users/elastic/delete');

        if (false == $this->callManager->hasFeature('security')) {
            $this->assertResponseStatusCodeSame(403);
        } else {
            $this->assertResponseStatusCodeSame(403);
        }
    }

    public function testDelete(): void
    {
        $this->client->request('GET', '/admin/elasticsearch-users/'.GENERATED_NAME.'/delete');

        if (false == $this->callManager->hasFeature('security')) {
            $this->assertResponseStatusCodeSame(403);
        } else {
            $this->assertResponseStatusCodeSame(302);
        }
    }
}
