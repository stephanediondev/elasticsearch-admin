<?php

namespace App\Tests\Controller;

class IndexControllerTest extends AbstractAppControllerTest
{
    // index
    public function testIndex()
    {
        $this->client->request('GET', '/admin/indices');

        $this->assertResponseStatusCodeSame(200);
        $this->assertPageTitleSame('Indices');
    }

    // reindex
    public function testReindex()
    {
        $this->client->request('GET', '/admin/indices/reindex');

        $this->assertResponseStatusCodeSame(200);
        $this->assertPageTitleSame('Indices - Reindex');
    }

    // create
    public function testCreate()
    {
        $this->client->request('GET', '/admin/indices/create');

        $this->assertResponseStatusCodeSame(200);
        $this->assertPageTitleSame('Indices - Create index');
    }

    // read
    public function testRead404()
    {
        $this->client->request('GET', '/admin/indices/'.uniqid());

        $this->assertResponseStatusCodeSame(404);
    }

    public function testRead()
    {
        $this->client->request('GET', '/admin/indices/elasticsearch-admin-test');

        $this->assertResponseStatusCodeSame(200);
    }

    // update
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
    }

    // settings
    public function testSettings404()
    {
        $this->client->request('GET', '/admin/indices/'.uniqid().'/settings');

        $this->assertResponseStatusCodeSame(404);
    }

    public function testSettings()
    {
        $this->client->request('GET', '/admin/indices/elasticsearch-admin-test/settings');

        $this->assertResponseStatusCodeSame(200);
    }

    // mappings
    public function testMappings404()
    {
        $this->client->request('GET', '/admin/indices/'.uniqid().'/mappings');

        $this->assertResponseStatusCodeSame(404);
    }

    public function testMappings()
    {
        $this->client->request('GET', '/admin/indices/elasticsearch-admin-test/mappings');

        $this->assertResponseStatusCodeSame(200);
    }

    // lifecycle
    public function testLifecycle404()
    {
        $this->client->request('GET', '/admin/indices/'.uniqid().'/lifecycle');

        $this->assertResponseStatusCodeSame(404);
    }

    public function testLifecycle()
    {
        $this->client->request('GET', '/admin/indices/elasticsearch-admin-test/lifecycle');

        $this->assertResponseStatusCodeSame(200);
    }

    // shards
    public function testShards404()
    {
        $this->client->request('GET', '/admin/indices/'.uniqid().'/shards');

        $this->assertResponseStatusCodeSame(404);
    }

    public function testShards()
    {
        $this->client->request('GET', '/admin/indices/elasticsearch-admin-test/shards');

        $this->assertResponseStatusCodeSame(200);
    }

    // search
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
    }

    // import-export
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
    }

    // aliases
    public function testAliases404()
    {
        $this->client->request('GET', '/admin/indices/'.uniqid().'/aliases');

        $this->assertResponseStatusCodeSame(404);
    }

    public function testAliases()
    {
        $this->client->request('GET', '/admin/indices/elasticsearch-admin-test/aliases');

        $this->assertResponseStatusCodeSame(200);
    }

    // aliases/create
    public function testCreateAlias404()
    {
        $this->client->request('GET', '/admin/indices/'.uniqid().'/aliases/create');

        $this->assertResponseStatusCodeSame(404);
    }

    public function testCreateAlias()
    {
        $this->client->request('GET', '/admin/indices/elasticsearch-admin-test/aliases/create');

        $this->assertResponseStatusCodeSame(200);
    }

    // refresh
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

    // empty
    public function testEmpty404()
    {
        $this->client->request('GET', '/admin/indices/'.uniqid().'/empty');

        $this->assertResponseStatusCodeSame(404);
    }

    public function testEmpty403()
    {
        $this->client->request('GET', '/admin/indices/.elasticsearch-admin-test/empty');

        $this->assertResponseStatusCodeSame(403);
    }

    public function testEmpty()
    {
        $this->client->request('GET', '/admin/indices/elasticsearch-admin-test/empty');

        $this->assertResponseStatusCodeSame(302);
    }

    // close
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

    // open
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

    // delete
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
