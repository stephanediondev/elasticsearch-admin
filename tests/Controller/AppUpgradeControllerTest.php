<?php

namespace App\Tests\Controller;

use App\Tests\Controller\AbstractAppControllerTest;

#[Route('/admin')]
class AppUpgradeControllerTest extends AbstractAppControllerTest
{
    #[Route('/app-upgrade', name: 'app_upgrade')]
    public function testIndex(): void
    {
        $this->client->request('GET', '/admin/app-upgrade');

        $this->assertResponseStatusCodeSame(200);
        $this->assertPageTitleSame('Upgrade');
        $this->assertSelectorTextSame('h1', 'Upgrade');
    }
}
