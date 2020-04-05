<?php

namespace App\Tests\Controller;

use App\Tests\Controller;

class ConsoleControllerTest extends AbstractAppControllerTest
{
    public function testIndex()
    {
        $this->client->request('GET', '/admin/console');

        $this->assertResponseStatusCodeSame(200);
    }
}
