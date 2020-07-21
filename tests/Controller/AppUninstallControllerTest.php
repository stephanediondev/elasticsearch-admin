<?php

namespace App\Tests\Controller;

/**
 * @Route("/admin")
 */
class AppUninstallControllerTest extends AbstractAppControllerTest
{
    /**
     * @Route("/app-uninstall", name="app_uninstall")
     */
    public function testIndex()
    {
        $this->client->request('GET', '/admin/app-uninstall');

        $this->assertResponseStatusCodeSame(200);
        $this->assertPageTitleSame('Uninstall');
    }
}
