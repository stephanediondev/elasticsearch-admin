<?php

namespace App\Tests\Controller;

class IlmControllerTest extends AbstractAppControllerTest
{
    public function testIndex()
    {
        $this->client->request('GET', '/admin/ilm');

        $this->assertResponseStatusCodeSame(200);
        $this->assertPageTitleSame('ILM policies');
    }

    public function testStatus()
    {
        $this->client->request('GET', '/admin/ilm/status');

        $this->assertResponseStatusCodeSame(200);
        $this->assertPageTitleSame('ILM policies - Status');
    }

    public function testRead404()
    {
        $this->client->request('GET', '/admin/ilm/'.uniqid());

        $this->assertResponseStatusCodeSame(404);
    }
}
