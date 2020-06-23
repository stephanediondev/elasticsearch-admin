<?php

namespace App\Tests\Controller;

/**
 * @Route("/admin")
 */
class IndexTemplateControllerTest extends AbstractAppControllerTest
{
    /**
     * @Route("/index-templates", name="index_templates")
     */
    public function testIndex()
    {
        $this->client->request('GET', '/admin/index-templates');

        $this->assertResponseStatusCodeSame(200);
        $this->assertPageTitleSame('Index templates');
    }

    /**
     * @Route("/index-templates/create", name="index_templates_create")
     */
    public function testCreate()
    {
        $this->client->request('GET', '/admin/index-templates/create');

        $this->assertResponseStatusCodeSame(200);
        $this->assertPageTitleSame('Index templates - Create index template');
    }

    /**
     * @Route("/index-templates/{name}", name="index_templates_read")
     */
    public function testRead404()
    {
        $this->client->request('GET', '/admin/index-templates/'.uniqid());

        $this->assertResponseStatusCodeSame(404);
    }

    /**
     * @Route("/index-templates/{name}/update", name="index_templates_update")
     */
    public function testUpdate404()
    {
        $this->client->request('GET', '/admin/index-templates/'.uniqid().'/update');

        $this->assertResponseStatusCodeSame(404);
    }
}
