<?php

namespace App\Tests\Controller;

use App\Tests\Controller;

class IndexTemplateControllerTest extends AbstractAppControllerTest
{
    public function testIndex()
    {
        $this->client->request('GET', '/admin/index-templates');

        $this->assertResponseStatusCodeSame(200);
    }

    public function testCreate()
    {
        $this->client->request('GET', '/admin/index-templates/create');

        $this->assertResponseStatusCodeSame(200);
    }
}
