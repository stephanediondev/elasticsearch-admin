<?php

namespace App\Tests\Controller;

use App\Tests\Controller\AbstractAppControllerTest;

class ElasticsearchConsoleControllerTest extends AbstractAppControllerTest
{
    public function testIndex(): void
    {
        $this->client->request('GET', '/admin/console');

        $this->assertResponseStatusCodeSame(200);
        $this->assertPageTitleSame('Console');
        $this->assertSelectorTextSame('h1', 'Console');
    }
}
