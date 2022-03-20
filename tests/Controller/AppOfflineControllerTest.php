<?php

namespace App\Tests\Controller;

use App\Tests\Controller\AbstractAppControllerTest;

/**
 * @Route("/admin")
 */
class AppOfflineControllerTest extends AbstractAppControllerTest
{
    /**
     * @Route("/app-uninstall", name="offline")
     */
    public function testIndex(): void
    {
        $this->client->request('GET', '/offline');

        $this->assertResponseStatusCodeSame(200);
        $this->assertPageTitleSame('Offline');
        $this->assertSelectorTextSame('h1', 'Offline');
    }
}
