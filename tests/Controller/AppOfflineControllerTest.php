<?php

namespace App\Tests\Controller;

/**
 * @Route("/admin")
 */
class AppOfflineControllerTest extends AbstractAppControllerTest
{
    /**
     * @Route("/app-uninstall", name="offline")
     */
    public function testIndex()
    {
        $this->client->request('GET', '/offline');

        $this->assertResponseStatusCodeSame(200);
        $this->assertPageTitleSame('Offline');
    }
}
