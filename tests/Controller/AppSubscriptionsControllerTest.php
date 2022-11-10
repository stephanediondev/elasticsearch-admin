<?php

namespace App\Tests\Controller;

use App\Tests\Controller\AbstractAppControllerTest;

class AppSubscriptionsControllerTest extends AbstractAppControllerTest
{
    public function testIndex(): void
    {
        $this->client->request('GET', '/admin/subscriptions');

        $this->assertResponseStatusCodeSame(200);
        $this->assertPageTitleSame('Subscriptions');
        $this->assertSelectorTextSame('h1', 'Subscriptions');
        $this->assertSelectorTextContains('h3', 'List');
    }

    public function testCreate403(): void
    {
        $this->client->request('GET', '/admin/subscriptions/create/'.uniqid());

        $this->assertResponseStatusCodeSame(403);
    }

    public function testCreatePush(): void
    {
        $this->client->request('GET', '/admin/subscriptions/create/push');

        $this->assertResponseStatusCodeSame(200);
        $this->assertPageTitleSame('Subscriptions - Create Push API');
        $this->assertSelectorTextSame('h1', 'Subscriptions');
        $this->assertSelectorTextSame('h3', 'Create Push API');
    }

    public function testCreateSlack(): void
    {
        $this->client->request('GET', '/admin/subscriptions/create/slack');

        $this->assertResponseStatusCodeSame(200);
        $this->assertPageTitleSame('Subscriptions - Create Slack Incoming Webhook');
        $this->assertSelectorTextSame('h1', 'Subscriptions');
        $this->assertSelectorTextSame('h3', 'Create Slack Incoming Webhook');
    }

    public function testCreateams(): void
    {
        $this->client->request('GET', '/admin/subscriptions/create/teams');

        $this->assertResponseStatusCodeSame(200);
        $this->assertPageTitleSame('Subscriptions - Create Microsoft Teams Incoming Webhook');
        $this->assertSelectorTextSame('h1', 'Subscriptions');
        $this->assertSelectorTextSame('h3', 'Create Microsoft Teams Incoming Webhook');
    }

    public function testUpdate404(): void
    {
        $this->client->request('GET', '/admin/subscriptions/'.uniqid().'/update');

        $this->assertResponseStatusCodeSame(404);
    }
}
