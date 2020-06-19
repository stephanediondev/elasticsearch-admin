<?php

namespace App\Tests\Controller;

class SnapshotControllerTest extends AbstractAppControllerTest
{
    public function testIndex()
    {
        $this->client->request('GET', '/admin/snapshots');

        $this->assertResponseStatusCodeSame(200);
        $this->assertPageTitleSame('Snapshots');
    }

    public function testCreate()
    {
        $this->client->request('GET', '/admin/snapshots/create');

        $this->assertResponseStatusCodeSame(200);
        $this->assertPageTitleSame('Snapshots - Create snapshot');
    }

    public function testRead404()
    {
        $this->client->request('GET', '/admin/snapshots/'.uniqid());

        $this->assertResponseStatusCodeSame(404);
    }

    public function testRestore404()
    {
        $this->client->request('GET', '/admin/snapshots/'.uniqid().'/restore');

        $this->assertResponseStatusCodeSame(404);
    }
}
