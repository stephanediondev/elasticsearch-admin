<?php

namespace App\Tests\Model;

use App\Model\AppNotificationModel;
use PHPUnit\Framework\TestCase;

class AppNotificationModelTest extends TestCase
{
    public function test()
    {
        $notification = new AppNotificationModel();
        $notification->setTitle('title');
        $notification->setContent('content');
        $notification->setColor('color');

        $this->assertEquals($notification->getTitle(), 'title');
        $this->assertEquals($notification->getContent(), 'content');
        $this->assertEquals($notification->getColor(), 'color');
    }
}
