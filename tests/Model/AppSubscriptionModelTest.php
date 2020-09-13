<?php

namespace App\Tests\Model;

use App\Model\AppSubscriptionModel;
use PHPUnit\Framework\TestCase;

class AppSubscriptionModelTest extends TestCase
{
    public function test()
    {
        $subscription = new AppSubscriptionModel();
        $subscription->setId('id');
        $subscription->setType('type');
        $subscription->setUserId('user-id');
        $subscription->setEndpoint('endpoint');
        $subscription->setPublicKey('public-key');
        $subscription->setAuthenticationSecret('authentication-secret');
        $subscription->setContentEncoding('content-encoding');
        $subscription->setIp('ip');
        $subscription->setOs('os');
        $subscription->setClient('client');
        $subscription->setCreatedAt(new \Datetime());

        $this->assertEquals($subscription->getId(), 'id');
        $this->assertEquals(strval($subscription), 'id');

        $this->assertEquals($subscription->getType(), 'type');
        $this->assertEquals($subscription->getUserId(), 'user-id');
        $this->assertEquals($subscription->getEndpoint(), 'endpoint');
        $this->assertEquals($subscription->getPublicKey(), 'public-key');
        $this->assertEquals($subscription->getAuthenticationSecret(), 'authentication-secret');
        $this->assertEquals($subscription->getContentEncoding(), 'content-encoding');
        $this->assertEquals($subscription->getIp(), 'ip');
        $this->assertEquals($subscription->getOs(), 'os');
        $this->assertEquals($subscription->getClient(), 'client');

        $this->assertInstanceOf('Datetime', $subscription->getCreatedAt());
    }
}
