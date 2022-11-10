<?php

namespace App\Tests\Controller;

use App\Tests\Controller\AbstractAppControllerTest;

class ElasticsearchIndexGraveyardControllerTest extends AbstractAppControllerTest
{
    public function testIndex(): void
    {
        $this->client->request('GET', '/admin/index-graveyard');

        if (false == $this->callManager->hasFeature('tombstones')) {
            $this->assertResponseStatusCodeSame(403);
        } else {
            $this->assertResponseStatusCodeSame(200);
            $this->assertPageTitleSame('Index graveyard');
            $this->assertSelectorTextSame('h1', 'Index graveyard');
            $this->assertSelectorTextContains('h3', 'List');
        }
    }
}
