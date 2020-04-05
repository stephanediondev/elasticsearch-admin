<?php

namespace App\Tests\Controller;

use App\Tests\Controller;

class CatControllerTest extends AbstractAppControllerTest
{
    public function testIndex()
    {
        $this->client->request('GET', '/admin/cat');

        $this->assertResponseStatusCodeSame(200);
    }
}
