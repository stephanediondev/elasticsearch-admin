<?php

namespace App\Tests\Controller;

class IndexTemplateControllerTest extends AbstractAppControllerTest
{
    public function testIndex()
    {
        $this->client->request('GET', '/admin/index-templates');

        $this->assertResponseStatusCodeSame(200);
        $this->assertPageTitleSame('Index templates');
    }

    public function testCreate()
    {
        $this->client->request('GET', '/admin/index-templates/create');

        $this->assertResponseStatusCodeSame(200);
        $this->assertPageTitleSame('Index templates - Create index template');
    }

    public function testRead404()
    {
        $this->client->request('GET', '/admin/index-templates/'.uniqid());

        $this->assertResponseStatusCodeSame(404);
    }

    public function testUpdate404()
    {
        $this->client->request('GET', '/admin/index-templates/'.uniqid().'/update');

        $this->assertResponseStatusCodeSame(404);
    }
}
