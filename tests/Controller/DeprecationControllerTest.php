<?php

namespace App\Tests\Controller;

class DeprecationControllerTest extends AbstractAppControllerTest
{
    public function testIndex()
    {
        $this->client->request('GET', '/admin/deprecations');

        $this->assertResponseStatusCodeSame(200);
    }
}
