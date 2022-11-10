<?php

namespace App\Tests\Controller;

use App\Tests\Controller\AbstractAppControllerTest;

class AppUninstallControllerTest extends AbstractAppControllerTest
{
    public function testIndex(): void
    {
        $this->client->request('GET', '/admin/app-uninstall');

        $this->assertResponseStatusCodeSame(200);
        $this->assertPageTitleSame('Uninstall');
        $this->assertSelectorTextSame('h1', 'Uninstall');
    }
}
