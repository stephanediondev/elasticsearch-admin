<?php

namespace App\Tests\Controller;

use App\Tests\Controller\AbstractAppControllerTest;

#[Route('/admin')]
class AppUninstallControllerTest extends AbstractAppControllerTest
{
    #[Route('/app-uninstall', name: 'app_uninstall')]
    public function testIndex(): void
    {
        $this->client->request('GET', '/admin/app-uninstall');

        $this->assertResponseStatusCodeSame(200);
        $this->assertPageTitleSame('Uninstall');
        $this->assertSelectorTextSame('h1', 'Uninstall');
    }
}
