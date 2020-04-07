<?php

namespace App\Tests\Controller;

use App\Model\CallRequestModel;
use App\Tests\Controller;

class NodeControllerTest extends AbstractAppControllerTest
{
    public function testIndex()
    {
        $this->client->request('GET', '/admin/nodes');

        $this->assertResponseStatusCodeSame(200);
    }

    public function testFetch()
    {
        $this->client->request('GET', '/admin/nodes/fetch');

        $this->assertResponseStatusCodeSame(200);
        $this->assertTrue($this->client->getResponse()->headers->contains('Content-Type', 'application/json'));
    }

    public function testRead404()
    {
        $this->client->request('GET', '/admin/nodes/'.uniqid());

        $this->assertResponseStatusCodeSame(404);
    }

    public function testRead()
    {
        $call = new CallRequestModel();
        $call->setPath('/_cat/master');
        $master = $this->callManager->call($call);

        $this->client->request('GET', '/admin/nodes/'.$master[0]['node']);

        $this->assertResponseStatusCodeSame(200);
    }

    public function testReadUsage()
    {
        $call = new CallRequestModel();
        $call->setPath('/_cat/master');
        $master = $this->callManager->call($call);

        $this->client->request('GET', '/admin/nodes/'.$master[0]['node'].'/usage');

        $this->assertResponseStatusCodeSame(200);
    }

    public function testReadPlugins()
    {
        $call = new CallRequestModel();
        $call->setPath('/_cat/master');
        $master = $this->callManager->call($call);

        $this->client->request('GET', '/admin/nodes/'.$master[0]['node'].'/plugins');

        $this->assertResponseStatusCodeSame(200);
    }
}
