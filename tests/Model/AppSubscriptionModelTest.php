<?php

namespace App\Tests\Model;

use App\Model\AppSubscriptionModel;
use PHPUnit\Framework\TestCase;

class AppSubscriptionModelTest extends TestCase
{
    public function test(): void
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
        $subscription->setNotifications(['notifications']);
        $subscription->setCreatedAt(new \Datetime());

        $this->assertEquals($subscription->getId(), 'id');
        $this->assertEquals(strval($subscription), 'id');
        $this->assertIsString($subscription->getId());

        $this->assertEquals($subscription->getType(), 'type');
        $this->assertIsString($subscription->getType());

        $this->assertEquals($subscription->getUserId(), 'user-id');
        $this->assertIsString($subscription->getUserId());

        $this->assertEquals($subscription->getEndpoint(), 'endpoint');
        $this->assertIsString($subscription->getEndpoint());

        $this->assertEquals($subscription->getPublicKey(), 'public-key');
        $this->assertIsString($subscription->getPublicKey());

        $this->assertEquals($subscription->getAuthenticationSecret(), 'authentication-secret');
        $this->assertIsString($subscription->getAuthenticationSecret());

        $this->assertEquals($subscription->getContentEncoding(), 'content-encoding');
        $this->assertIsString($subscription->getContentEncoding());

        $this->assertEquals($subscription->getIp(), 'ip');
        $this->assertIsString($subscription->getIp());

        $this->assertEquals($subscription->getOs(), 'os');
        $this->assertIsString($subscription->getOs());

        $this->assertEquals($subscription->getClient(), 'client');
        $this->assertIsString($subscription->getClient());

        $this->assertEquals($subscription->getNotifications(), ['notifications']);
        $this->assertIsArray($subscription->getNotifications());

        $this->assertInstanceOf('Datetime', $subscription->getCreatedAt());

        $this->assertEquals($subscription->getJson(), [
            'user_id' => $subscription->getUserId(),
            'type' => $subscription->getType(),
            'endpoint' => $subscription->getEndpoint(),
            'public_key' => $subscription->getPublicKey(),
            'authentication_secret' => $subscription->getAuthenticationSecret(),
            'content_encoding' => $subscription->getContentEncoding(),
            'ip' => $subscription->getIp(),
            'os' => $subscription->getOs(),
            'client' => $subscription->getClient(),
            'notifications' => $subscription->getNotifications(),
            'created_at' => $subscription->getCreatedAt()->format('Y-m-d H:i:s'),
        ]);
        $this->assertIsArray($subscription->getJson());
    }
}
