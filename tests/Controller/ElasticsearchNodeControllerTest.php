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
    }

    /**
     * @Route("/nodes/fetch", name="nodes_fetch")
     */
    public function testFetch()
    {
        $this->client->request('GET', '/admin/nodes/fetch');

        $this->assertResponseStatusCodeSame(200);
        $this->assertTrue($this->client->getResponse()->headers->contains('Content-Type', 'application/json'));
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

        $this->client->request('GET', '/admin/nodes/'.$masterNode.'/plugins');

        $this->assertResponseStatusCodeSame(200);
        $this->assertPageTitleSame('Nodes - '.$masterNode.' - Plugins');
    }

    /**
     * @Route("/nodes/{node}/usage", name="nodes_read_usage")
     */
    public function testReadUsage404()
    {
        $this->client->request('GET', '/admin/nodes/'.uniqid().'/usage');

        $this->assertResponseStatusCodeSame(404);
    }

    public function testReadUsage()
    {
        $masterNode = $this->callManager->getMasterNode();

        $this->client->request('GET', '/admin/nodes/'.$masterNode.'/usage');

        $this->assertResponseStatusCodeSame(200);
        $this->assertPageTitleSame('Nodes - '.$masterNode.' - Usage');
    }

    /**
     * @Route("/nodes/{node}/reload-secure-settings", name="nodes_reload_secure_settings")
     */
    public function testReadReloadSecureSettings404()
    {
        $this->client->request('GET', '/admin/nodes/'.uniqid().'/reload-secure-settings');

        if (false == $this->callManager->hasFeature('reload_secure_settings')) {
            $this->assertResponseStatusCodeSame(403);
        } else {
            $this->assertResponseStatusCodeSame(404);
        }
    }

    public function testReadReloadSecureSettings()
    {
        $masterNode = $this->callManager->getMasterNode();

        $this->client->request('GET', '/admin/nodes/'.$masterNode.'/reload-secure-settings');

        if (false == $this->callManager->hasFeature('reload_secure_settings')) {
            $this->assertResponseStatusCodeSame(403);
        } else {
            $this->assertResponseStatusCodeSame(200);
            $this->assertPageTitleSame('Nodes - '.$masterNode.' - Reload secure settings');
        }
    }
}
