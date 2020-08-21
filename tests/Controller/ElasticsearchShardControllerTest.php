<?php

namespace App\Tests\Controller;

/**
 * @Route("/admin")
 */
class ElasticsearchShardControllerTest extends AbstractAppControllerTest
{
    /**
     * @Route("/shards", name="shards")
     */
    public function testIndex()
    {
        $this->client->request('GET', '/admin/shards');

        $this->assertResponseStatusCodeSame(200);
        $this->assertPageTitleSame('Shards - Stats');
    }
}
