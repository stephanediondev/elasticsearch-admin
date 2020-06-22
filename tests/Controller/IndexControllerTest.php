<?php

namespace App\Tests\Controller;

class IndexControllerTest extends AbstractAppControllerTest
{
    public function testIndex()
    {
        $this->client->request('GET', '/admin/indices');

        $this->assertResponseStatusCodeSame(200);
        $this->assertPageTitleSame('Indices');
    }

    public function testReindex()
    {
        $this->client->request('GET', '/admin/indices/reindex');

        $this->assertResponseStatusCodeSame(200);
        $this->assertPageTitleSame('Indices - Reindex');
    }

    public function testCreate()
    {
        $this->client->request('GET', '/admin/indices/create');

        $this->assertResponseStatusCodeSame(200);
        $this->assertPageTitleSame('Indices - Create index');
    }

    public function testRead404()
    {
        $this->client->request('GET', '/admin/indices/'.uniqid());

        $this->assertResponseStatusCodeSame(404);
    }

    public function testUpdate404()
    {
        $this->client->request('GET', '/admin/indices/'.uniqid().'/update');

        $this->assertResponseStatusCodeSame(404);
    }

    public function testUpdate403()
    {
        $this->client->request('GET', '/admin/indices/.test/update');

        $this->assertResponseStatusCodeSame(403);
    }

    public function testSettings404()
    {
        $this->client->request('GET', '/admin/indices/'.uniqid().'/settings');

        $this->assertResponseStatusCodeSame(404);
    }

    public function testMappings404()
    {
        $this->client->request('GET', '/admin/indices/'.uniqid().'/mappings');

        $this->assertResponseStatusCodeSame(404);
    }

    public function testLifecycle404()
    {
        $this->client->request('GET', '/admin/indices/'.uniqid().'/lifecycle');

        $this->assertResponseStatusCodeSame(404);
    }

    public function testShards404()
    {
        $this->client->request('GET', '/admin/indices/'.uniqid().'/shards');

        $this->assertResponseStatusCodeSame(404);
    }

    public function testSearch404()
    {
        $this->client->request('GET', '/admin/indices/'.uniqid().'/search');

        $this->assertResponseStatusCodeSame(404);
    }

    public function testSearch403()
    {
        $this->client->request('GET', '/admin/indices/.test/search');

        $this->assertResponseStatusCodeSame(403);
    }

    public function testImportExport404()
    {
        $this->client->request('GET', '/admin/indices/'.uniqid().'/import-export');

        $this->assertResponseStatusCodeSame(404);
    }

    public function testImportExport403()
    {
        $this->client->request('GET', '/admin/indices/.test/import-export');

        $this->assertResponseStatusCodeSame(403);
    }

    public function testAliases404()
    {
        $this->client->request('GET', '/admin/indices/'.uniqid().'/aliases');

        $this->assertResponseStatusCodeSame(404);
    }

    public function testCreateAlias404()
    {
        $this->client->request('GET', '/admin/indices/'.uniqid().'/aliases/create');

        $this->assertResponseStatusCodeSame(404);
    }

    public function testRefresh404()
    {
        $this->client->request('GET', '/admin/indices/'.uniqid().'/refresh');

        $this->assertResponseStatusCodeSame(404);
    }

    public function testRefresh403()
    {
        $this->client->request('GET', '/admin/indices/.test/refresh');

        $this->assertResponseStatusCodeSame(403);
    }

    public function testEmpty404()
    {
        $this->client->request('GET', '/admin/indices/'.uniqid().'/empty');

        $this->assertResponseStatusCodeSame(404);
    }

    public function testEmpty403()
    {
        $this->client->request('GET', '/admin/indices/.test/empty');

        $this->assertResponseStatusCodeSame(403);
    }
}
