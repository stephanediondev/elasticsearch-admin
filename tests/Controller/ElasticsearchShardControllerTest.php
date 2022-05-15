<?php

namespace App\Tests\Controller;

use App\Tests\Controller\AbstractAppControllerTest;

#[Route('/admin')]
class ElasticsearchShardControllerTest extends AbstractAppControllerTest
{
    #[Route('/shards', name: 'shards')]
    public function testIndex(): void
    {
        $this->client->request('GET', '/admin/shards');

        $this->assertResponseStatusCodeSame(200);
        $this->assertPageTitleSame('Shards');
        $this->assertSelectorTextSame('h1', 'Shards');
        $this->assertSelectorTextContains('h3', 'List');
    }

    #[Route('/shards/stats', name: 'shards_stats')]
    public function testStats(): void
    {
        $this->client->request('GET', '/admin/shards/stats');

        $this->assertResponseStatusCodeSame(200);
        $this->assertPageTitleSame('Shards - Stats');
        $this->assertSelectorTextSame('h1', 'Shards');
        $this->assertSelectorTextSame('h3', 'Stats');
    }
}
