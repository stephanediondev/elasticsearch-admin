<?php

namespace App\Tests\Controller;

use App\Tests\Controller\AbstractAppControllerTest;

class ElasticsearchCatControllerTest extends AbstractAppControllerTest
{
    public function testIndex(): void
    {
        $this->client->request('GET', '/admin/cat');

        $this->assertResponseStatusCodeSame(200);
        $this->assertPageTitleSame('Compact and aligned text (CAT) APIs');
        $this->assertSelectorTextSame('h1', 'Compact and aligned text (CAT) APIs');
    }
}
