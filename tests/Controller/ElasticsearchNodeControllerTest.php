<?php

namespace App\Tests\Controller;

use App\Model\CallRequestModel;

/**
 * @Route("/admin")
 */
class ElasticsearchNodeControllerTest extends AbstractAppControllerTest
{
    /**
     * @Route("/nodes", name="nodes")
     */
    public function testIndex()
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
    public function testStats()
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
    public function testReadReloadSecureSettings()
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
    public function testRead404()
    {
        $this->client->request('GET', '/admin/nodes/'.uniqid());

        $this->assertResponseStatusCodeSame(404);
    }

    public function testRead()
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
    public function testReadSettings404()
    {
        $this->client->request('GET', '/admin/nodes/'.uniqid().'/settings');

        $this->assertResponseStatusCodeSame(404);
    }

    public function testReadSettings()
    {
        $masterNode = $this->callManager->getMasterNode();

        $node = $this->elasticsearchNodeManager->getByName($masterNode);

        $this->client->request('GET', '/admin/nodes/'.$masterNode.'/settings');

        $this->assertResponseStatusCodeSame(200);
        $this->assertPageTitleSame('Nodes - '.$masterNode.' - Settings');
        $this->assertSelectorTextSame('h1', 'Nodes');
        $this->assertSelectorTextSame('h2', $masterNode.' Master');
        if ($node->getSettings()) {
            $this->assertSelectorTextSame('h3', 'Settings '.count($node->getSettings()));
        } else {
            $this->assertSelectorTextSame('h3', 'Settings 0');
        }
    }

    /**
     * @Route("/nodes/{node}/plugins", name="nodes_read_plugins")
     */
    public function testReadPlugins404()
    {
        $this->client->request('GET', '/admin/nodes/'.uniqid().'/plugins');

        $this->assertResponseStatusCodeSame(404);
    }

    public function testReadPlugins()
    {
        $masterNode = $this->callManager->getMasterNode();

        $node = $this->elasticsearchNodeManager->getByName($masterNode);

        $this->client->request('GET', '/admin/nodes/'.$masterNode.'/plugins');

        $this->assertResponseStatusCodeSame(200);
        $this->assertPageTitleSame('Nodes - '.$masterNode.' - Plugins');
        $this->assertSelectorTextSame('h1', 'Nodes');
        $this->assertSelectorTextSame('h2', $masterNode.' Master');
        if ($node->getPlugins()) {
            $this->assertSelectorTextSame('h3', 'Plugins '.count($node->getPlugins()));
        } else {
            $this->assertSelectorTextSame('h3', 'Plugins 0');
        }
    }

    /**
     * @Route("/nodes/{node}/usage", name="nodes_read_usage")
     */
    public function testReadUsage404()
    {
        $this->client->request('GET', '/admin/nodes/'.uniqid().'/usage');

        if (false == $this->callManager->hasFeature('node_usage')) {
            $this->assertResponseStatusCodeSame(403);
        } else {
            $this->assertResponseStatusCodeSame(404);
        }
    }

    public function testReadUsage()
    {
        $masterNode = $this->callManager->getMasterNode();

        $callRequest = new CallRequestModel();
        $callRequest->setPath('/_nodes/'.$masterNode.'/usage');
        $callResponse = $this->callManager->call($callRequest);
        $usage = $callResponse->getContent();
        $usage = $usage['nodes'][key($usage['nodes'])];

        $this->client->request('GET', '/admin/nodes/'.$masterNode.'/usage');

        if (false == $this->callManager->hasFeature('node_usage')) {
            $this->assertResponseStatusCodeSame(403);
        } else {
            $this->assertResponseStatusCodeSame(200);
            $this->assertPageTitleSame('Nodes - '.$masterNode.' - Usage');
            $this->assertSelectorTextSame('h1', 'Nodes');
            $this->assertSelectorTextSame('h2', $masterNode.' Master');
            if (true === isset($usage['rest_actions']) && true === is_array($usage['rest_actions'])) {
                $this->assertSelectorTextSame('h3', 'Usage '.count($usage['rest_actions']));
            } else {
                $this->assertSelectorTextSame('h3', 'Usage 0');
            }
        }
    }
}
