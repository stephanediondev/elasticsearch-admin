<?php

namespace App\Tests\Controller;

use App\Tests\Controller\AbstractAppControllerTest;

class AppOfflineControllerTest extends AbstractAppControllerTest
{
    public function testIndex(): void
    {
        $this->client->request('GET', '/offline');

        $this->assertResponseStatusCodeSame(200);
        $this->assertPageTitleSame('Offline');
        $this->assertSelectorTextSame('h1', 'Offline');
    }
}
