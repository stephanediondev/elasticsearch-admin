<?php

namespace App\Tests\Controller;

use App\Tests\Controller\AbstractAppControllerTest;

class ElasticsearchSqlControllerTest extends AbstractAppControllerTest
{
    public function testIndex(): void
    {
        $this->client->request('GET', '/admin/sql');

        if (false == $this->callManager->hasFeature('sql')) {
            $this->assertResponseStatusCodeSame(403);
        } else {
            $this->assertResponseStatusCodeSame(200);
            $this->assertPageTitleSame('SQL access');
            $this->assertSelectorTextSame('h1', 'SQL access');
        }
    }
}
