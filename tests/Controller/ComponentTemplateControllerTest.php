<?php

namespace App\Tests\Controller;

/**
 * @Route("/admin")
 */
class ComponentTemplateControllerTest extends AbstractAppControllerTest
{
    /**
     * @Route("/component-templates", name="component_templates")
     */
    public function testComponent()
    {
        $this->client->request('GET', '/admin/component-templates');

        $this->assertResponseStatusCodeSame(200);
        $this->assertPageTitleSame('Component templates');
    }

    /**
     * @Route("/component-templates/create", name="component_templates_create")
     */
    public function testCreate()
    {
        $this->client->request('GET', '/admin/component-templates/create');

        $this->assertResponseStatusCodeSame(200);
        $this->assertPageTitleSame('Component templates - Create component template');
    }

    /**
     * @Route("/component-templates/{name}", name="component_templates_read")
     */
    public function testRead404()
    {
        $this->client->request('GET', '/admin/component-templates/'.uniqid());

        $this->assertResponseStatusCodeSame(404);
    }

    public function testRead()
    {
        $this->client->request('GET', '/admin/component-templates/elasticsearch-admin-test');

        $this->assertResponseStatusCodeSame(200);
        $this->assertPageTitleSame('Component templates - elasticsearch-admin-test');
    }

    /**
     * @Route("/component-templates/{name}/update", name="component_templates_update")
     */
    public function testUpdate404()
    {
        $this->client->request('GET', '/admin/component-templates/'.uniqid().'/update');

        $this->assertResponseStatusCodeSame(404);
    }

    public function testUpdate403()
    {
        $this->client->request('GET', '/admin/component-templates/.elasticsearch-admin-test/update');

        $this->assertResponseStatusCodeSame(403);
    }

    public function testUpdate()
    {
        $this->client->request('GET', '/admin/component-templates/elasticsearch-admin-test/update');

        $this->assertResponseStatusCodeSame(200);
        $this->assertPageTitleSame('Component templates - elasticsearch-admin-test - Update');
    }

    /**
     * @Route("/component-templates/{name}/settings", name="component_templates_read_settings")
     */
    public function testSettings404()
    {
        $this->client->request('GET', '/admin/component-templates/'.uniqid().'/settings');

        $this->assertResponseStatusCodeSame(404);
    }

    public function testSettings()
    {
        $this->client->request('GET', '/admin/component-templates/elasticsearch-admin-test/settings');

        $this->assertResponseStatusCodeSame(200);
        $this->assertPageTitleSame('Component templates - elasticsearch-admin-test - Settings');
    }

    /**
     * @Route("/component-templates/{name}/mappings", name="component_templates_read_mappings")
     */
    public function testMappings404()
    {
        $this->client->request('GET', '/admin/component-templates/'.uniqid().'/mappings');

        $this->assertResponseStatusCodeSame(404);
    }

    public function testMappings()
    {
        $this->client->request('GET', '/admin/component-templates/elasticsearch-admin-test/mappings');

        $this->assertResponseStatusCodeSame(200);
        $this->assertPageTitleSame('Component templates - elasticsearch-admin-test - Mappings');
    }

    /**
     * @Route("/component-templates/{name}/delete", name="component_templates_delete")
     */
    public function testDelete404()
    {
        $this->client->request('GET', '/admin/component-templates/'.uniqid().'/delete');

        $this->assertResponseStatusCodeSame(404);
    }

    public function testDelete403()
    {
        $this->client->request('GET', '/admin/component-templates/.elasticsearch-admin-test/delete');

        $this->assertResponseStatusCodeSame(403);
    }

    public function testDelete()
    {
        $this->client->request('GET', '/admin/component-templates/elasticsearch-admin-test/delete');

        $this->assertResponseStatusCodeSame(302);
    }
}

