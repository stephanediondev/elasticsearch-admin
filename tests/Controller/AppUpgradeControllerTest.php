<?php

namespace App\Tests\Controller;

/**
 * @Route("/admin")
 */
class AppUpgradeControllerTest extends AbstractAppControllerTest
{
    /**
     * @Route("/app-upgrade", name="app_upgrade")
     */
    public function testIndex()
    {
        $this->client->request('GET', '/admin/app-upgrade');

        $this->assertResponseStatusCodeSame(200);
        $this->assertPageTitleSame('Upgrade');
    }
}
