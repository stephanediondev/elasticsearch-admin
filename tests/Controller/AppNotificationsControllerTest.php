<?php

namespace App\Tests\Controller;

use App\Tests\Controller\AbstractAppControllerTest;

#[Route('/admin')]
class AppNotificationsControllerTest extends AbstractAppControllerTest
{
    #[Route('/app-notifications', name: 'app_notifications')]
    public function testIndex(): void
    {
        $this->client->request('GET', '/admin/app-notifications');

        $this->assertResponseStatusCodeSame(200);
        $this->assertPageTitleSame('Notifications');
        $this->assertSelectorTextSame('h1', 'Notifications');
        $this->assertSelectorTextContains('h3', 'List');
    }
}
