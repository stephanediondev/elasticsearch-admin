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
        $notification->setBody('body');
        $notification->setColor('color');

        $this->assertEquals($notification->getTitle(), 'title');
        $this->assertEquals($notification->getBody(), 'body');
        $this->assertEquals($notification->getColor(), 'color');
    }
}
