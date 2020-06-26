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

    public function testRead()
    {
        $this->client->request('GET', '/admin/index-templates/elasticsearch-admin-test');

        $this->assertResponseStatusCodeSame(200);
        $this->assertPageTitleSame('Index templates - elasticsearch-admin-test');
    }

    /**
     * @Route("/index-templates/{name}/update", name="index_templates_update")
     */
    public function testUpdate404()
    {
        $this->client->request('GET', '/admin/index-templates/'.uniqid().'/update');

        $this->assertResponseStatusCodeSame(404);
    }

    public function testUpdate403()
    {
        $this->client->request('GET', '/admin/index-templates/.elasticsearch-admin-test/update');

        $this->assertResponseStatusCodeSame(403);
    }

    public function testUpdate()
    {
        $this->client->request('GET', '/admin/index-templates/elasticsearch-admin-test/update');

        $this->assertResponseStatusCodeSame(200);
        $this->assertPageTitleSame('Index templates - elasticsearch-admin-test - Update');
    }

    /**
     * @Route("/index-templates/{name}/delete", name="index_templates_delete")
     */
    public function testDelete404()
    {
        $this->client->request('GET', '/admin/index-templates/'.uniqid().'/delete');

        $this->assertResponseStatusCodeSame(404);
    }

    public function testDelete403()
    {
        $this->client->request('GET', '/admin/index-templates/.elasticsearch-admin-test/delete');

        $this->assertResponseStatusCodeSame(403);
    }

    public function testDelete()
    {
        $this->client->request('GET', '/admin/index-templates/elasticsearch-admin-test/delete');

        $this->assertResponseStatusCodeSame(302);
    }
}

