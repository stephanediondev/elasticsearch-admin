<?php

namespace App\Tests\Controller;

use App\Tests\Controller;

class TaskControllerTest extends AbstractAppControllerTest
{
    public function testIndex()
    {
        $this->client->request('GET', '/admin/tasks');

        $this->assertResponseStatusCodeSame(200);
    }
}
