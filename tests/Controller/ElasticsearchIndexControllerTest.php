<?php

namespace App\Tests\Controller;

/**
 * @Route("/admin")
 */
class ElasticsearchIndexControllerTest extends AbstractAppControllerTest
{
    /**
     * @Route("/indices", name="indices")
     */
    public function testIndex()
    {
        $this->client->request('GET', '/admin/indices');

        $this->assertResponseStatusCodeSame(200);
        $this->assertPageTitleSame('Indices');
    }

    /**
     * @Route("/indices/reindex", name="indices_reindex")
     */
    public function testReindex()
    {
        $this->client->request('GET', '/admin/indices/reindex');

        $this->assertResponseStatusCodeSame(200);
        $this->assertPageTitleSame('Indices - Reindex');
    }

    /**
     * @Route("/indices/create", name="indices_create")
     */
    public function testCreate()
    {
        $this->client->request('GET', '/admin/indices/create');

        $this->assertResponseStatusCodeSame(200);
        $this->assertPageTitleSame('Indices - Create index');
    }

    /**
     * @Route("/indices/{index}", name="indices_read")
     */
    public function testRead404()
    {
        $this->client->request('GET', '/admin/indices/'.uniqid());

        $this->assertResponseStatusCodeSame(404);
    }

    public function testRead()
    {
        $this->client->request('GET', '/admin/indices/elasticsearch-admin-test');

        $this->assertResponseStatusCodeSame(200);
        $this->assertPageTitleSame('Indices - elasticsearch-admin-test');
    }

    /**
     * @Route("/indices/{index}/update", name="indices_update")
     */
    public function testUpdate404()
    {
        $this->client->request('GET', '/admin/indices/'.uniqid().'/update');

        $this->assertResponseStatusCodeSame(404);
    }

    public function testUpdate403()
    {
        $this->client->request('GET', '/admin/indices/.elasticsearch-admin-test/update');

        $this->assertResponseStatusCodeSame(403);
    }

    public function testUpdate()
    {
        $this->client->request('GET', '/admin/indices/elasticsearch-admin-test/update');

        $this->assertResponseStatusCodeSame(200);
        $this->assertPageTitleSame('Indices - elasticsearch-admin-test - Update');
    }

    /**
     * @Route("/indices/{index}/settings", name="indices_read_settings")
     */
    public function testSettings404()
    {
        $this->client->request('GET', '/admin/indices/'.uniqid().'/settings');

        $this->assertResponseStatusCodeSame(404);
    }

    public function testSettings()
    {
        $this->client->request('GET', '/admin/indices/elasticsearch-admin-test/settings');

        $this->assertResponseStatusCodeSame(200);
        $this->assertPageTitleSame('Indices - elasticsearch-admin-test - Settings');
    }

    /**
     * @Route("/indices/{index}/mappings", name="indices_read_mappings")
     */
    public function testMappings404()
    {
        $this->client->request('GET', '/admin/indices/'.uniqid().'/mappings');

        $this->assertResponseStatusCodeSame(404);
    }

    public function testMappings()
    {
        $this->client->request('GET', '/admin/indices/elasticsearch-admin-test/mappings');

        $this->assertResponseStatusCodeSame(200);
        $this->assertPageTitleSame('Indices - elasticsearch-admin-test - Mappings');
    }

    /**
     * @Route("/indices/{index}/lifecycle", name="indices_read_lifecycle")
     */
    public function testLifecycle404()
    {
        $this->client->request('GET', '/admin/indices/'.uniqid().'/lifecycle');

        if (false == $this->callManager->hasFeature('ilm')) {
            $this->assertResponseStatusCodeSame(403);
        } else {
            $this->assertResponseStatusCodeSame(404);
        }
    }

    public function testLifecycle()
    {
        $this->client->request('GET', '/admin/indices/elasticsearch-admin-test/lifecycle');

        if (false == $this->callManager->hasFeature('ilm')) {
            $this->assertResponseStatusCodeSame(403);
        } else {
            $this->assertResponseStatusCodeSame(200);
            $this->assertPageTitleSame('Indices - elasticsearch-admin-test - Lifecycle');
        }
    }

    /**
     * @Route("/indices/{index}/shards", name="indices_read_shards")
     */
    public function testShards404()
    {
        $this->client->request('GET', '/admin/indices/'.uniqid().'/shards');

        $this->assertResponseStatusCodeSame(404);
    }

    public function testShards()
    {
        $this->client->request('GET', '/admin/indices/elasticsearch-admin-test/shards');

        $this->assertResponseStatusCodeSame(200);
        $this->assertPageTitleSame('Indices - elasticsearch-admin-test - Shards');
    }

    /**
     * @Route("/indices/{index}/search", name="indices_read_search")
     */
    public function testSearch404()
    {
        $this->client->request('GET', '/admin/indices/'.uniqid().'/search');

        $this->assertResponseStatusCodeSame(404);
    }

    public function testSearch403()
    {
        $this->client->request('GET', '/admin/indices/.elasticsearch-admin-test/search');

        $this->assertResponseStatusCodeSame(403);
    }

    public function testSearch()
    {
        $this->client->request('GET', '/admin/indices/elasticsearch-admin-test/search');

        $this->assertResponseStatusCodeSame(200);
        $this->assertPageTitleSame('Indices - elasticsearch-admin-test - Search');
    }

    /**
     * @Route("/indices/{index}/import-export", name="indices_read_import_export")
     */
    public function testImportExport404()
    {
        $this->client->request('GET', '/admin/indices/'.uniqid().'/import-export');

        $this->assertResponseStatusCodeSame(404);
    }

    public function testImportExport403()
    {
        $this->client->request('GET', '/admin/indices/.elasticsearch-admin-test/import-export');

        $this->assertResponseStatusCodeSame(403);
    }

    public function testImportExport()
    {
        $this->client->request('GET', '/admin/indices/elasticsearch-admin-test/import-export');

        $this->assertResponseStatusCodeSame(200);
        $this->assertPageTitleSame('Indices - elasticsearch-admin-test - Import / Export');
    }

    /**
     * @Route("/indices/{index}/aliases", name="indices_read_aliases")
     */
    public function testAliases404()
    {
        $this->client->request('GET', '/admin/indices/'.uniqid().'/aliases');

        $this->assertResponseStatusCodeSame(404);
    }

    public function testAliases()
    {
        $this->client->request('GET', '/admin/indices/elasticsearch-admin-test/aliases');

        $this->assertResponseStatusCodeSame(200);
        $this->assertPageTitleSame('Indices - elasticsearch-admin-test - Aliases');
    }

    /**
     * @Route("/indices/{index}/aliases/create", name="indices_aliases_create")
     */
    public function testCreateAlias404()
    {
        $this->client->request('GET', '/admin/indices/'.uniqid().'/aliases/create');

        $this->assertResponseStatusCodeSame(404);
    }

    public function testCreateAlias()
    {
        $this->client->request('GET', '/admin/indices/elasticsearch-admin-test/aliases/create');

        $this->assertResponseStatusCodeSame(200);
        $this->assertPageTitleSame('Indices - elasticsearch-admin-test - Create alias');
    }

    /**
     * @Route("/indices/{index}/refresh", name="indices_refresh")
     */
    public function testRefresh404()
    {
        $this->client->request('GET', '/admin/indices/'.uniqid().'/refresh');

        $this->assertResponseStatusCodeSame(404);
    }

    public function testRefresh403()
    {
        $this->client->request('GET', '/admin/indices/.elasticsearch-admin-test/refresh');

        $this->assertResponseStatusCodeSame(403);
    }

    public function testRefresh()
    {
        $this->client->request('GET', '/admin/indices/elasticsearch-admin-test/refresh');

        $this->assertResponseStatusCodeSame(302);
    }

    /**
     * @Route("/indices/{index}/empty", name="indices_empty")
     */
    public function testEmpty404()
    {
        $this->client->request('GET', '/admin/indices/'.uniqid().'/empty');

        if (false == $this->callManager->hasFeature('delete_by_query')) {
            $this->assertResponseStatusCodeSame(403);
        } else {
            $this->assertResponseStatusCodeSame(404);
        }
    }

    public function testEmpty403()
    {
        $this->client->request('GET', '/admin/indices/.elasticsearch-admin-test/empty');

        if (false == $this->callManager->hasFeature('delete_by_query')) {
            $this->assertResponseStatusCodeSame(403);
        } else {
            $this->assertResponseStatusCodeSame(403);
        }
    }

    public function testEmpty()
    {
        $this->client->request('GET', '/admin/indices/elasticsearch-admin-test/empty');

        if (false == $this->callManager->hasFeature('delete_by_query')) {
            $this->assertResponseStatusCodeSame(403);
        } else {
            $this->assertResponseStatusCodeSame(302);
        }
    }

    /**
     * @Route("/indices/{index}/close", name="indices_close")
     */
    public function testClose404()
    {
        $this->client->request('GET', '/admin/indices/'.uniqid().'/close');

        $this->assertResponseStatusCodeSame(404);
    }

    public function testClose403()
    {
        $this->client->request('GET', '/admin/indices/.elasticsearch-admin-test/close');

        $this->assertResponseStatusCodeSame(403);
    }

    public function testClose()
    {
        $this->client->request('GET', '/admin/indices/elasticsearch-admin-test/close');

        $this->assertResponseStatusCodeSame(302);
    }

    /**
     * @Route("/indices/{index}/open", name="indices_open")
     */
    public function testOpen404()
    {
        $this->client->request('GET', '/admin/indices/'.uniqid().'/open');

        $this->assertResponseStatusCodeSame(404);
    }

    public function testOpen403()
    {
        $this->client->request('GET', '/admin/indices/.elasticsearch-admin-test/open');

        $this->assertResponseStatusCodeSame(403);
    }

    public function testOpen()
    {
        $this->client->request('GET', '/admin/indices/elasticsearch-admin-test/open');

        $this->assertResponseStatusCodeSame(302);
    }

    /**
     * @Route("/indices/{index}/freeze", name="indices_freeze")
     */
    public function testFreeze404()
    {
        $this->client->request('GET', '/admin/indices/'.uniqid().'/freeze');

        if (false == $this->callManager->hasFeature('freeze_unfreeze')) {
            $this->assertResponseStatusCodeSame(403);
        } else {
            $this->assertResponseStatusCodeSame(404);
        }
    }

    public function testFreeze403()
    {
        $this->client->request('GET', '/admin/indices/.elasticsearch-admin-test/freeze');

        if (false == $this->callManager->hasFeature('freeze_unfreeze')) {
            $this->assertResponseStatusCodeSame(403);
        } else {
            $this->assertResponseStatusCodeSame(403);
        }
    }

    public function testFreeze()
    {
        $this->client->request('GET', '/admin/indices/elasticsearch-admin-test/freeze');

        if (false == $this->callManager->hasFeature('freeze_unfreeze')) {
            $this->assertResponseStatusCodeSame(403);
        } else {
            $this->assertResponseStatusCodeSame(302);
        }
    }

    /**
     * @Route("/indices/{index}/unfreeze", name="indices_unfreeze")
     */
    public function testUnfreeze404()
    {
        $this->client->request('GET', '/admin/indices/'.uniqid().'/unfreeze');

        if (false == $this->callManager->hasFeature('freeze_unfreeze')) {
            $this->assertResponseStatusCodeSame(403);
        } else {
            $this->assertResponseStatusCodeSame(404);
        }
    }

    public function testUnfreeze403()
    {
        $this->client->request('GET', '/admin/indices/.elasticsearch-admin-test/unfreeze');

        if (false == $this->callManager->hasFeature('freeze_unfreeze')) {
            $this->assertResponseStatusCodeSame(403);
        } else {
            $this->assertResponseStatusCodeSame(403);
        }
    }

    public function testUnfreeze()
    {
        $this->client->request('GET', '/admin/indices/elasticsearch-admin-test/unfreeze');

        if (false == $this->callManager->hasFeature('freeze_unfreeze')) {
            $this->assertResponseStatusCodeSame(403);
        } else {
            $this->assertResponseStatusCodeSame(302);
        }
    }

    /**
     * @Route("/indices/{index}/delete", name="indices_delete")
     */
    public function testDelete404()
    {
        $this->client->request('GET', '/admin/indices/'.uniqid().'/delete');

        $this->assertResponseStatusCodeSame(404);
    }

    public function testDelete403()
    {
        $this->client->request('GET', '/admin/indices/.elasticsearch-admin-test/delete');

        $this->assertResponseStatusCodeSame(403);
    }

    public function testDelete()
    {
        $this->client->request('GET', '/admin/indices/elasticsearch-admin-test/delete');

        $this->assertResponseStatusCodeSame(302);
    }
}
