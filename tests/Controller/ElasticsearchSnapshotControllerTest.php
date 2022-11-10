<?php

namespace App\Tests\Controller;

use App\Tests\Controller\AbstractAppControllerTest;

class ElasticsearchSnapshotControllerTest extends AbstractAppControllerTest
{
    public function testIndex(): void
    {
        $this->client->request('GET', '/admin/snapshots');

        $this->assertResponseStatusCodeSame(200);
        $this->assertPageTitleSame('Snapshots');
        $this->assertSelectorTextSame('h1', 'Snapshots');
        $this->assertSelectorTextContains('h3', 'List');
    }

    public function testStats(): void
    {
        $this->client->request('GET', '/admin/snapshots/stats');

        $this->assertResponseStatusCodeSame(200);
        $this->assertPageTitleSame('Snapshots - Stats');
        $this->assertSelectorTextSame('h1', 'Snapshots');
        $this->assertSelectorTextSame('h3', 'Stats');
    }

    public function testCreate(): void
    {
        $this->client->request('GET', '/admin/snapshots/create');

        $this->assertResponseStatusCodeSame(200);
        $this->assertPageTitleSame('Snapshots - Create snapshot');
        $this->assertSelectorTextSame('h1', 'Snapshots');
        $this->assertSelectorTextSame('h3', 'Create snapshot');
    }

    public function testRead404(): void
    {
        $this->client->request('GET', '/admin/snapshots/'.uniqid().'/'.uniqid());

        $this->assertResponseStatusCodeSame(404);
    }

    public function testRestore404(): void
    {
        $this->client->request('GET', '/admin/snapshots/'.uniqid().'/'.uniqid().'/restore');

        $this->assertResponseStatusCodeSame(404);
    }

    public function testClone404(): void
    {
        $this->client->request('GET', '/admin/snapshots/'.uniqid().'/'.uniqid().'/clone');

        if (false == $this->callManager->hasFeature('clone_snapshot')) {
            $this->assertResponseStatusCodeSame(403);
        } else {
            $this->assertResponseStatusCodeSame(404);
        }
    }
}
