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
        $notification->setIcon('icon');

        $this->assertEquals($notification->getTitle(), 'title');
        $this->assertEquals($notification->getBody(), 'body');
        $this->assertEquals($notification->getIcon(), 'icon');
    }
}
