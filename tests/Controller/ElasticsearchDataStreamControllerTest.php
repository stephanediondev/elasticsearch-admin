<?php

namespace App\Tests\Controller;

use App\Tests\Controller\AbstractAppControllerTest;

class ElasticsearchDataStreamControllerTest extends AbstractAppControllerTest
{
    public function testIndex(): void
    {
        $this->client->request('GET', '/admin/data-streams');

        if (false == $this->callManager->hasFeature('data_streams')) {
            $this->assertResponseStatusCodeSame(403);
        } else {
            $this->assertResponseStatusCodeSame(200);
            $this->assertPageTitleSame('Data streams');
            $this->assertSelectorTextSame('h1', 'Data streams');
            $this->assertSelectorTextContains('h3', 'List');
        }
    }

    public function testCreate(): void
    {
        $this->client->request('GET', '/admin/data-streams/create');

        if (false == $this->callManager->hasFeature('data_streams')) {
            $this->assertResponseStatusCodeSame(403);
        } else {
            $this->assertResponseStatusCodeSame(200);
            $this->assertPageTitleSame('Data streams - Create data stream');
            $this->assertSelectorTextSame('h1', 'Data streams');
            $this->assertSelectorTextSame('h3', 'Create data stream');
        }
    }

    public function testRead404(): void
    {
        $this->client->request('GET', '/admin/data-streams/'.uniqid());

        if (false == $this->callManager->hasFeature('data_streams')) {
            $this->assertResponseStatusCodeSame(403);
        } else {
            $this->assertResponseStatusCodeSame(404);
        }
    }

    public function testStats404(): void
    {
        $this->client->request('GET', '/admin/data-streams/'.uniqid().'/stats');

        if (false == $this->callManager->hasFeature('data_streams')) {
            $this->assertResponseStatusCodeSame(403);
        } else {
            $this->assertResponseStatusCodeSame(404);
        }
    }

    public function testDelete404(): void
    {
        $this->client->request('GET', '/admin/data-streams/'.uniqid().'/delete');

        if (false == $this->callManager->hasFeature('data_streams')) {
            $this->assertResponseStatusCodeSame(403);
        } else {
            $this->assertResponseStatusCodeSame(404);
        }
    }
}
