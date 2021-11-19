<?php

namespace App\Tests\Controller;

/**
 * @Route("/admin")
 */
class ElasticsearchNodeControllerTest extends AbstractAppControllerTest
{
    /**
     * @Route("/nodes", name="nodes")
     */
    public function testIndex(): void
    {
        $this->client->request('GET', '/admin/nodes');

        $this->assertResponseStatusCodeSame(200);
        $this->assertPageTitleSame('Nodes');
        $this->assertSelectorTextSame('h1', 'Nodes');
        $this->assertSelectorTextContains('h3', 'List');
    }

    /**
     * @Route("/nodes/stats", name="nodes_stats")
     */
    public function testStats(): void
    {
        $this->client->request('GET', '/admin/nodes/stats');

        $this->assertResponseStatusCodeSame(200);
        $this->assertPageTitleSame('Nodes - Stats');
        $this->assertSelectorTextSame('h1', 'Nodes');
        $this->assertSelectorTextSame('h3', 'Stats');
    }

    /**
     * @Route("/nodes/reload-secure-settings", name="nodes_reload_secure_settings")
     */
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

    /**
     * @Route("/nodes/{node}", name="nodes_read")
     */
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

    /**
     * @Route("/nodes/{node}/settings", name="nodes_read_settings")
     */
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

    /**
     * @Route("/nodes/{node}/plugins", name="nodes_read_plugins")
     */
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

    /**
     * @Route("/nodes/{node}/usage", name="nodes_read_usage")
     */
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
