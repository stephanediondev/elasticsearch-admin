<?php

namespace App\Tests\Controller;

/**
 * @Route("/admin")
 */
class IndexTemplateControllerLegacyTest extends AbstractAppControllerTest
{
    /**
     * @Route("/index-templates-legacy", name="index_templates_legacy")
     */
    public function testIndex()
    {
        $this->client->request('GET', '/admin/index-templates-legacy');

        $this->assertResponseStatusCodeSame(200);
        $this->assertPageTitleSame('Index templates (legacy)');
    }

    /**
     * @Route("/index-templates-legacy/create", name="index_templates_legacy_create")
     */
    public function testCreate()
    {
        $this->client->request('GET', '/admin/index-templates-legacy/create');

        $this->assertResponseStatusCodeSame(200);
        $this->assertPageTitleSame('Index templates (legacy) - Create index template');
    }

    public function testCreateCopy404()
    {
        $this->client->request('GET', '/admin/index-templates-legacy/create?template='.uniqid());

        $this->assertResponseStatusCodeSame(404);
    }

    public function testCreateCopy()
    {
        $this->client->request('GET', '/admin/index-templates-legacy/create?template=elasticsearch-admin-test');

        $this->assertResponseStatusCodeSame(200);
        $this->assertPageTitleSame('Index templates (legacy) - Create index template');
    }

    /**
     * @Route("/index-templates-legacy/{name}", name="index_templates_legacy_read")
     */
    public function testRead404()
    {
        $this->client->request('GET', '/admin/index-templates-legacy/'.uniqid());

        $this->assertResponseStatusCodeSame(404);
    }

    public function testRead()
    {
        $this->client->request('GET', '/admin/index-templates-legacy/elasticsearch-admin-test');

        $this->assertResponseStatusCodeSame(200);
        $this->assertPageTitleSame('Index templates (legacy) - elasticsearch-admin-test');
    }

    /**
     * @Route("/index-templates-legacy/{name}/update", name="index_templates_legacy_update")
     */
    public function testUpdate404()
    {
        $this->client->request('GET', '/admin/index-templates-legacy/'.uniqid().'/update');

        $this->assertResponseStatusCodeSame(404);
    }

    public function testUpdate403()
    {
        $this->client->request('GET', '/admin/index-templates-legacy/.elasticsearch-admin-test/update');

        $this->assertResponseStatusCodeSame(403);
    }

    public function testUpdate()
    {
        $this->client->request('GET', '/admin/index-templates-legacy/elasticsearch-admin-test/update');

        $this->assertResponseStatusCodeSame(200);
        $this->assertPageTitleSame('Index templates (legacy) - elasticsearch-admin-test - Update');
    }

    /**
     * @Route("/index-templates-legacy/{name}/settings", name="index_templates_legacy_read_settings")
     */
    public function testSettings404()
    {
        $this->client->request('GET', '/admin/index-templates-legacy/'.uniqid().'/settings');

        $this->assertResponseStatusCodeSame(404);
    }

    public function testSettings()
    {
        $this->client->request('GET', '/admin/index-templates-legacy/elasticsearch-admin-test/settings');

        $this->assertResponseStatusCodeSame(200);
        $this->assertPageTitleSame('Index templates (legacy) - elasticsearch-admin-test - Settings');
    }

    /**
     * @Route("/index-templates-legacy/{name}/mappings", name="index_templates_legacy_read_mappings")
     */
    public function testMappings404()
    {
        $this->client->request('GET', '/admin/index-templates-legacy/'.uniqid().'/mappings');

        $this->assertResponseStatusCodeSame(404);
    }

    public function testMappings()
    {
        $this->client->request('GET', '/admin/index-templates-legacy/elasticsearch-admin-test/mappings');

        $this->assertResponseStatusCodeSame(200);
        $this->assertPageTitleSame('Index templates (legacy) - elasticsearch-admin-test - Mappings');
    }

    /**
     * @Route("/index-templates-legacy/{name}/delete", name="index_templates_legacy_delete")
     */
    public function testDelete404()
    {
        $this->client->request('GET', '/admin/index-templates-legacy/'.uniqid().'/delete');

        $this->assertResponseStatusCodeSame(404);
    }

    public function testDelete403()
    {
        $this->client->request('GET', '/admin/index-templates-legacy/.elasticsearch-admin-test/delete');

        $this->assertResponseStatusCodeSame(403);
    }

    public function testDelete()
    {
        $this->client->request('GET', '/admin/index-templates-legacy/elasticsearch-admin-test/delete');

        $this->assertResponseStatusCodeSame(302);
    }
}
