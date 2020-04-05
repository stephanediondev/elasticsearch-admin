<?php

namespace App\Tests\Controller;

use App\Tests\Controller;

class LicenseControllerTest extends AbstractAppControllerTest
{
    public function testIndex()
    {
        $this->client->request('GET', '/admin/license');

        $this->assertResponseStatusCodeSame(200);
    }
}
