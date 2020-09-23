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
        $this->assertSelectorTextSame('h1', 'Indices');
    }

    /**
     * @Route("/indices/stats", name="indices_stats")
     */
    public function testStats()
    {
        $this->client->request('GET', '/admin/indices/stats');

        $this->assertResponseStatusCodeSame(200);
        $this->assertPageTitleSame('Indices - Stats');
        $this->assertSelectorTextSame('h1', 'Indices');
    }

    /**
     * @Route("/indices/reindex", name="indices_reindex")
     */
    public function testReindex()
    {
        $this->client->request('GET', '/admin/indices/reindex');

        $this->assertResponseStatusCodeSame(200);
        $this->assertPageTitleSame('Indices - Reindex');
        $this->assertSelectorTextSame('h1', 'Indices');
    }

    /**
     * @Route("/indices/create", name="indices_create")
     */
    public function testCreate()
    {
        $this->client->request('GET', '/admin/indices/create');

        $this->assertResponseStatusCodeSame(200);
        $this->assertPageTitleSame('Indices - Create index');
        $this->assertSelectorTextSame('h1', 'Indices');
        $this->assertSelectorTextSame('h3', 'Create index');

        $values = [
            'data[name]' => GENERATED_NAME,
        ];
        if (true === $this->callManager->checkVersion('7.0')) {
            $values['data[mappings]'] = file_get_contents(__DIR__.'/../../src/DataFixtures/es-test-mappings.json');
        }
        $this->client->submitForm('Submit', $values);

        $this->assertResponseStatusCodeSame(302);

        $this->client->followRedirect();
        $this->assertPageTitleSame('Indices - '.GENERATED_NAME);
        $this->assertSelectorTextSame('h1', 'Indices');
    }

    public function testCreateSystem()
    {
        $this->client->request('GET', '/admin/indices/create');

        $this->assertResponseStatusCodeSame(200);
        $this->assertPageTitleSame('Indices - Create index');
        $this->assertSelectorTextSame('h1', 'Indices');
        $this->assertSelectorTextSame('h3', 'Create index');

        $values = [
            'data[name]' => GENERATED_NAME_SYSTEM,
        ];
        if (true === $this->callManager->checkVersion('7.0')) {
            $values['data[mappings]'] = file_get_contents(__DIR__.'/../../src/DataFixtures/es-test-mappings.json');
        }
        $this->client->submitForm('Submit', $values);

        $this->assertResponseStatusCodeSame(302);

        $this->client->followRedirect();
        $this->assertPageTitleSame('Indices - '.GENERATED_NAME_SYSTEM);
        $this->assertSelectorTextSame('h1', 'Indices');
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
        $this->client->request('GET', '/admin/indices/'.GENERATED_NAME);

        $this->assertResponseStatusCodeSame(200);
        $this->assertPageTitleSame('Indices - '.GENERATED_NAME);
        $this->assertSelectorTextSame('h1', 'Indices');
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
        $this->client->request('GET', '/admin/indices/'.GENERATED_NAME_SYSTEM.'/update');

        $this->assertResponseStatusCodeSame(403);
    }

    public function testUpdate()
    {
        $this->client->request('GET', '/admin/indices/'.GENERATED_NAME.'/update');

        $this->assertResponseStatusCodeSame(200);
        $this->assertPageTitleSame('Indices - '.GENERATED_NAME.' - Update');
        $this->assertSelectorTextSame('h1', 'Indices');
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
        $this->client->request('GET', '/admin/indices/'.GENERATED_NAME.'/settings');

        $this->assertResponseStatusCodeSame(200);
        $this->assertPageTitleSame('Indices - '.GENERATED_NAME.' - Settings');
        $this->assertSelectorTextSame('h1', 'Indices');
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
        $this->client->request('GET', '/admin/indices/'.GENERATED_NAME.'/mappings');

        $this->assertResponseStatusCodeSame(200);
        $this->assertPageTitleSame('Indices - '.GENERATED_NAME.' - Mappings');
        $this->assertSelectorTextSame('h1', 'Indices');
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
        $this->client->request('GET', '/admin/indices/'.GENERATED_NAME.'/lifecycle');

        if (false == $this->callManager->hasFeature('ilm')) {
            $this->assertResponseStatusCodeSame(403);
        } else {
            $this->assertResponseStatusCodeSame(200);
            $this->assertPageTitleSame('Indices - '.GENERATED_NAME.' - Lifecycle');
            $this->assertSelectorTextSame('h1', 'Indices');
        }
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
        $this->client->request('GET', '/admin/indices/'.GENERATED_NAME_SYSTEM.'/search');

        $this->assertResponseStatusCodeSame(403);
    }

    public function testSearch()
    {
        $this->client->request('GET', '/admin/indices/'.GENERATED_NAME.'/search');

        $this->assertResponseStatusCodeSame(200);
        $this->assertPageTitleSame('Indices - '.GENERATED_NAME.' - Search');
        $this->assertSelectorTextSame('h1', 'Indices');
    }

    /**
     * @Route("/indices/{index}/file-import", name="indices_read_import")
     */
    public function testImport404()
    {
        $this->client->request('GET', '/admin/indices/'.uniqid().'/file-import');

        $this->assertResponseStatusCodeSame(404);
    }

    public function testImport403()
    {
        $this->client->request('GET', '/admin/indices/'.GENERATED_NAME_SYSTEM.'/file-import');

        $this->assertResponseStatusCodeSame(403);
    }

    public function testImport()
    {
        $this->client->request('GET', '/admin/indices/'.GENERATED_NAME.'/file-import');

        $this->assertResponseStatusCodeSame(200);
        $this->assertPageTitleSame('Indices - '.GENERATED_NAME.' - Import from file');
        $this->assertSelectorTextSame('h1', 'Indices');
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
        $this->client->request('GET', '/admin/indices/'.GENERATED_NAME.'/aliases');

        $this->assertResponseStatusCodeSame(200);
        $this->assertPageTitleSame('Indices - '.GENERATED_NAME.' - Aliases');
        $this->assertSelectorTextSame('h1', 'Indices');
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
        $this->client->request('GET', '/admin/indices/'.GENERATED_NAME.'/aliases/create');

        $this->assertResponseStatusCodeSame(200);
        $this->assertPageTitleSame('Indices - '.GENERATED_NAME.' - Aliases');
        $this->assertSelectorTextSame('h1', 'Indices');
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
        $this->client->request('GET', '/admin/indices/'.GENERATED_NAME_SYSTEM.'/refresh');

        $this->assertResponseStatusCodeSame(403);
    }

    public function testRefresh()
    {
        $this->client->request('GET', '/admin/indices/'.GENERATED_NAME.'/refresh');

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
        $this->client->request('GET', '/admin/indices/'.GENERATED_NAME_SYSTEM.'/empty');

        if (false == $this->callManager->hasFeature('delete_by_query')) {
            $this->assertResponseStatusCodeSame(403);
        } else {
            $this->assertResponseStatusCodeSame(403);
        }
    }

    public function testEmpty()
    {
        $this->client->request('GET', '/admin/indices/'.GENERATED_NAME.'/empty');

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
        $this->client->request('GET', '/admin/indices/'.GENERATED_NAME_SYSTEM.'/close');

        $this->assertResponseStatusCodeSame(403);
    }

    public function testClose()
    {
        $this->client->request('GET', '/admin/indices/'.GENERATED_NAME.'/close');

        $clusterSettings = $this->elasticsearchClusterManager->getClusterSettings();

        if (true == isset($clusterSettings['cluster.indices.close.enable']) && 'false' == $clusterSettings['cluster.indices.close.enable']) {
            $this->assertResponseStatusCodeSame(403);
        } else {
            $this->assertResponseStatusCodeSame(302);
        }
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
        $this->client->request('GET', '/admin/indices/'.GENERATED_NAME_SYSTEM.'/open');

        $this->assertResponseStatusCodeSame(403);
    }

    public function testOpen()
    {
        $this->client->request('GET', '/admin/indices/'.GENERATED_NAME.'/open');

        $clusterSettings = $this->elasticsearchClusterManager->getClusterSettings();

        if (true == isset($clusterSettings['cluster.indices.close.enable']) && 'false' == $clusterSettings['cluster.indices.close.enable']) {
            $this->assertResponseStatusCodeSame(403);
        } else {
            $this->assertResponseStatusCodeSame(302);
        }
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
        $this->client->request('GET', '/admin/indices/'.GENERATED_NAME_SYSTEM.'/freeze');

        if (false == $this->callManager->hasFeature('freeze_unfreeze')) {
            $this->assertResponseStatusCodeSame(403);
        } else {
            $this->assertResponseStatusCodeSame(403);
        }
    }

    public function testFreeze()
    {
        $this->client->request('GET', '/admin/indices/'.GENERATED_NAME.'/freeze');

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
        $this->client->request('GET', '/admin/indices/'.GENERATED_NAME_SYSTEM.'/unfreeze');

        if (false == $this->callManager->hasFeature('freeze_unfreeze')) {
            $this->assertResponseStatusCodeSame(403);
        } else {
            $this->assertResponseStatusCodeSame(403);
        }
    }

    public function testUnfreeze()
    {
        $this->client->request('GET', '/admin/indices/'.GENERATED_NAME.'/unfreeze');

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
        $this->client->request('GET', '/admin/indices/'.GENERATED_NAME_SYSTEM.'/delete');

        $this->assertResponseStatusCodeSame(403);
    }

    public function testDelete()
    {
        $this->client->request('GET', '/admin/indices/'.GENERATED_NAME.'/delete');

        $this->assertResponseStatusCodeSame(302);
    }
}
