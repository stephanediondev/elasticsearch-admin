<?php

namespace App\Tests\Controller;

use App\Tests\Controller;

class PipelineControllerTest extends AbstractAppControllerTest
{
    public function testIndex()
    {
        $this->client->request('GET', '/admin/pipelines');

        $this->assertResponseStatusCodeSame(200);
    }
}
