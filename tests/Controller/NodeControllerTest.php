<?php

namespace App\Tests\Controller;

use App\Model\CallRequestModel;
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
        $callRequest = new CallRequestModel();
        $callRequest->setPath('/_cat/master');
        $callResponse = $this->callManager->call($callRequest);
        $master = $callResponse->getContent();

        $this->client->request('GET', '/admin/nodes/'.$master[0]['node']);

        $this->assertResponseStatusCodeSame(200);
    }

    public function testReadUsage()
    {
        $callRequest = new CallRequestModel();
        $callRequest->setPath('/_cat/master');
        $callResponse = $this->callManager->call($callRequest);
        $master = $callResponse->getContent();

        $this->client->request('GET', '/admin/nodes/'.$master[0]['node'].'/usage');

        $this->assertResponseStatusCodeSame(200);
    }

    public function testReadPlugins()
    {
        $callRequest = new CallRequestModel();
        $callRequest->setPath('/_cat/master');
        $callResponse = $this->callManager->call($callRequest);
        $master = $callResponse->getContent();

        $this->client->request('GET', '/admin/nodes/'.$master[0]['node'].'/plugins');

        $this->assertResponseStatusCodeSame(200);
    }
}
