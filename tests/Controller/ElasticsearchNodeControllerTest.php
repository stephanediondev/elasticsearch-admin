<?php

namespace App\Tests\Controller;

use App\Tests\Controller\AbstractAppControllerTest;

class ElasticsearchNodeControllerTest extends AbstractAppControllerTest
{
    public function testIndex(): void
    {
        $this->client->request('GET', '/admin/nodes');

        $this->assertResponseStatusCodeSame(200);
        $this->assertPageTitleSame('Nodes');
        $this->assertSelectorTextSame('h1', 'Nodes');
        $this->assertSelectorTextContains('h3', 'List');
    }

    public function testStats(): void
    {
        $this->client->request('GET', '/admin/nodes/stats');

        $this->assertResponseStatusCodeSame(200);
        $this->assertPageTitleSame('Nodes - Stats');
        $this->assertSelectorTextSame('h1', 'Nodes');
        $this->assertSelectorTextSame('h3', 'Stats');
    }

    public function testReadReloadSecureSettings(): void
    {
        $this->client->request('GET', '/admin/nodes/reload-secure-settings');

        if (false == $this->callManager->hasFeature('reload_secure_settings')) {
            $this->assertResponseStatusCodeSame(403);
        } else {
            $this->assertResponseStatusCodeSame(200);
            $this->assertPageTitleSame('Nodes - Reload secure settings');
            $this->assertSelectorTextSame('h1', 'Nodes');
            $this->assertSelectorTextSame('h3', 'Reload secure settings');
        }
    }

    public function testRead404(): void
    {
        $this->client->request('GET', '/admin/nodes/'.uniqid());

        $this->assertResponseStatusCodeSame(404);
    }

    public function testRead(): void
    {
        $masterNode = $this->callManager->getMasterNode();

        $this->client->request('GET', '/admin/nodes/'.$masterNode);

        $this->assertResponseStatusCodeSame(200);
        $this->assertPageTitleSame('Nodes - '.$masterNode);
        $this->assertSelectorTextSame('h1', 'Nodes');
        $this->assertSelectorTextSame('h2', $masterNode.' Master');
        $this->assertSelectorTextSame('h3', 'Summary');
    }

    public function testReadSettings404(): void
    {
        $this->client->request('GET', '/admin/nodes/'.uniqid().'/settings');

        $this->assertResponseStatusCodeSame(404);
    }

    public function testReadSettings(): void
    {
        $masterNode = $this->callManager->getMasterNode();

        $this->client->request('GET', '/admin/nodes/'.$masterNode.'/settings');

        $this->assertResponseStatusCodeSame(200);
        $this->assertPageTitleSame('Nodes - '.$masterNode.' - Settings');
        $this->assertSelectorTextSame('h1', 'Nodes');
        $this->assertSelectorTextSame('h2', $masterNode.' Master');
        $this->assertSelectorTextContains('h3', 'Settings');
    }

    public function testReadPlugins404(): void
    {
        $this->client->request('GET', '/admin/nodes/'.uniqid().'/plugins');

        $this->assertResponseStatusCodeSame(404);
    }

    public function testReadPlugins(): void
    {
        $masterNode = $this->callManager->getMasterNode();

        $this->client->request('GET', '/admin/nodes/'.$masterNode.'/plugins');

        $this->assertResponseStatusCodeSame(200);
        $this->assertPageTitleSame('Nodes - '.$masterNode.' - Plugins');
        $this->assertSelectorTextSame('h1', 'Nodes');
        $this->assertSelectorTextSame('h2', $masterNode.' Master');
        $this->assertSelectorTextContains('h3', 'Plugins');
    }

    public function testReadUsage404(): void
    {
        $this->client->request('GET', '/admin/nodes/'.uniqid().'/usage');

        if (false == $this->callManager->hasFeature('node_usage')) {
            $this->assertResponseStatusCodeSame(403);
        } else {
            $this->assertResponseStatusCodeSame(404);
        }
    }

    public function testReadUsage(): void
    {
        $masterNode = $this->callManager->getMasterNode();

        $this->client->request('GET', '/admin/nodes/'.$masterNode.'/usage');

        if (false == $this->callManager->hasFeature('node_usage')) {
            $this->assertResponseStatusCodeSame(403);
        } else {
            $this->assertResponseStatusCodeSame(200);
            $this->assertPageTitleSame('Nodes - '.$masterNode.' - Usage');
            $this->assertSelectorTextSame('h1', 'Nodes');
            $this->assertSelectorTextSame('h2', $masterNode.' Master');
            $this->assertSelectorTextContains('h3', 'Usage');
        }
    }
}
