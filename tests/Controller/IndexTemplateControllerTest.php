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

    public function testCreateCopy404()
    {
        $this->client->request('GET', '/admin/index-templates/create?template='.uniqid());

        $this->assertResponseStatusCodeSame(404);
    }

    public function testCreateCopy()
    {
        $this->client->request('GET', '/admin/index-templates/create?template=elasticsearch-admin-test');

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
     * @Route("/index-templates/{name}/settings", name="index_templates_read_settings")
     */
    public function testSettings404()
    {
        $this->client->request('GET', '/admin/index-templates/'.uniqid().'/settings');

        $this->assertResponseStatusCodeSame(404);
    }

    public function testSettings()
    {
        $this->client->request('GET', '/admin/index-templates/elasticsearch-admin-test/settings');

        $this->assertResponseStatusCodeSame(200);
        $this->assertPageTitleSame('Index templates - elasticsearch-admin-test - Settings');
    }

    /**
     * @Route("/index-templates/{name}/mappings", name="index_templates_read_mappings")
     */
    public function testMappings404()
    {
        $this->client->request('GET', '/admin/index-templates/'.uniqid().'/mappings');

        $this->assertResponseStatusCodeSame(404);
    }

    public function testMappings()
    {
        $this->client->request('GET', '/admin/index-templates/elasticsearch-admin-test/mappings');

        $this->assertResponseStatusCodeSame(200);
        $this->assertPageTitleSame('Index templates - elasticsearch-admin-test - Mappings');
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

