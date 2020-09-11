<?php

namespace App\Tests\Controller;

/**
 * @Route("/admin")
 */
class AppSubscriptionsControllerTest extends AbstractAppControllerTest
{
    /**
     * @Route("/subscriptions", name="app_subscriptions")
     */
    public function testIndex()
    {
        $this->client->request('GET', '/admin/subscriptions');

        $this->assertResponseStatusCodeSame(200);
        $this->assertPageTitleSame('Subscriptions');
    }
}
