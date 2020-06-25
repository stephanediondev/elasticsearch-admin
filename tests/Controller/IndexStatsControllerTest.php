<?php

namespace App\Tests\Controller;

/**
 * @Route("/admin")
 */
class IndexStatsControllerTest extends AbstractAppControllerTest
{
    /**
     * @Route("/indices/stats", name="indices_stats")
     */
    public function testIndex()
    {
        $this->client->request('GET', '/admin/indices/stats');

        $this->assertResponseStatusCodeSame(200);
        $this->assertPageTitleSame('Indices - Stats');
    }
}
