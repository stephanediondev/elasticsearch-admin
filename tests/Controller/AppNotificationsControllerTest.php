<?php

namespace App\Tests\Controller;

use App\Tests\Controller\AbstractAppControllerTest;

class AppNotificationsControllerTest extends AbstractAppControllerTest
{
    public function testIndex(): void
    {
        $this->client->request('GET', '/admin/app-notifications');

        $this->assertResponseStatusCodeSame(200);
        $this->assertPageTitleSame('Notifications');
        $this->assertSelectorTextSame('h1', 'Notifications');
        $this->assertSelectorTextContains('h3', 'List');
    }
}
