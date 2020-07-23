<?php

namespace App\Tests\Controller;

/**
 * @Route("/admin")
 */
class ElasticsearchIndexGraveyardControllerTest extends AbstractAppControllerTest
{
    /**
     * @Route("/index-graveyard", name="index_graveyard")
     */
    public function testIndex()
    {
        $this->client->request('GET', '/admin/index-graveyard');

        $this->assertResponseStatusCodeSame(200);
        $this->assertPageTitleSame('Index graveyard');
    }
}
