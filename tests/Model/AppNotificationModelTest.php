<?php

namespace App\Tests\Model;

use App\Model\AppNotificationModel;
use PHPUnit\Framework\TestCase;

class AppNotificationModelTest extends TestCase
{
    public function test(): void
    {
        $notification = new AppNotificationModel();
        $notification->setId('id');
        $notification->setType('type');
        $notification->setCluster('cluster');
        $notification->setTitle('title');
        $notification->setContent('content');
        $notification->setColor('color');
        $notification->setCreatedAt(new \Datetime());

        $this->assertEquals($notification->getId(), 'id');
        $this->assertIsString($notification->getId());

        $this->assertEquals($notification->getType(), 'type');
        $this->assertIsString($notification->getType());

        $this->assertEquals($notification->getCluster(), 'cluster');
        $this->assertIsString($notification->getCluster());

        $this->assertEquals($notification->getTitle(), 'title');
        $this->assertIsString($notification->getTitle());

        $this->assertEquals($notification->getContent(), 'content');
        $this->assertIsString($notification->getContent());

        $this->assertEquals($notification->getColor(), 'color');
        $this->assertIsString($notification->getColor());

        $this->assertInstanceOf('Datetime', $notification->getCreatedAt());

        $this->assertEquals($notification->getJson(), [
            'type' => $notification->getType(),
            'cluster' => $notification->getCluster(),
            'title' => $notification->getTitle(),
            'content' => $notification->getContent(),
            'color' => $notification->getColor(),
            'created_at' => $notification->getCreatedAt()->format('Y-m-d H:i:s'),
        ]);
        $this->assertIsArray($notification->getJson());
    }
}
