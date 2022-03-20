<?php

namespace App\Tests\Controller;

use App\Tests\Controller\AbstractAppControllerTest;

use App\Model\CallRequestModel;

/**
 * @Route("/admin")
 */
class ElasticsearchIndexTemplateLegacyControllerTest extends AbstractAppControllerTest
{
    /**
     * @Route("/index-templates-legacy", name="index_templates_legacy")
     */
    public function testIndex(): void
    {
        $this->client->request('GET', '/admin/index-templates-legacy');

        $this->assertResponseStatusCodeSame(200);
        $this->assertPageTitleSame('Legacy index templates');
        $this->assertSelectorTextSame('h1', 'Legacy index templates');
        $this->assertSelectorTextContains('h3', 'List');
    }

    /**
     * @Route("/index-templates-legacy/create", name="index_templates_legacy_create")
     */
    public function testCreate(): void
    {
        $this->client->request('GET', '/admin/index-templates-legacy/create');

        $this->assertResponseStatusCodeSame(200);
        $this->assertPageTitleSame('Legacy index templates - Create legacy index template');
        $this->assertSelectorTextSame('h1', 'Legacy index templates');
        $this->assertSelectorTextSame('h3', 'Create legacy index template');

        $values = [
            'data[name]' => GENERATED_NAME,
        ];
        if (true === $this->callManager->hasFeature('multiple_patterns')) {
            $values['data[index_patterns]'] = GENERATED_NAME;
        } else {
            $values['data[template]'] = GENERATED_NAME;
        }
        $this->client->submitForm('Submit', $values);

        $this->assertResponseStatusCodeSame(302);

        $this->client->followRedirect();
        $this->assertPageTitleSame('Legacy index templates - '.GENERATED_NAME);
        $this->assertSelectorTextSame('h1', 'Legacy index templates');
        $this->assertSelectorTextSame('h2', GENERATED_NAME);
        $this->assertSelectorTextSame('h3', 'Summary');
    }

    public function testCreateSystem(): void
    {
        $this->client->request('GET', '/admin/index-templates-legacy/create');

        $this->assertResponseStatusCodeSame(200);
        $this->assertPageTitleSame('Legacy index templates - Create legacy index template');
        $this->assertSelectorTextSame('h1', 'Legacy index templates');
        $this->assertSelectorTextSame('h3', 'Create legacy index template');

        $values = [
            'data[name]' => GENERATED_NAME_SYSTEM,
        ];
        if (true === $this->callManager->hasFeature('multiple_patterns')) {
            $values['data[index_patterns]'] = GENERATED_NAME_SYSTEM;
        } else {
            $values['data[template]'] = GENERATED_NAME_SYSTEM;
        }
        $this->client->submitForm('Submit', $values);

        $this->assertResponseStatusCodeSame(302);

        $this->client->followRedirect();
        $this->assertPageTitleSame('Legacy index templates - '.GENERATED_NAME_SYSTEM);
        $this->assertSelectorTextSame('h1', 'Legacy index templates');
        $this->assertSelectorTextSame('h2', GENERATED_NAME_SYSTEM);
        $this->assertSelectorTextSame('h3', 'Summary');
    }

    public function testCreateCopy404(): void
    {
        $this->client->request('GET', '/admin/index-templates-legacy/create?template='.uniqid());

        $this->assertResponseStatusCodeSame(404);
    }

    public function testCreateCopy403(): void
    {
        $this->client->request('GET', '/admin/index-templates-legacy/create?template='.GENERATED_NAME_SYSTEM);

        $this->assertResponseStatusCodeSame(403);
    }

    public function testCreateCopy(): void
    {
        $this->client->request('GET', '/admin/index-templates-legacy/create?template='.GENERATED_NAME);

        $this->assertResponseStatusCodeSame(200);
        $this->assertPageTitleSame('Legacy index templates - Create legacy index template');
        $this->assertSelectorTextSame('h1', 'Legacy index templates');
        $this->assertSelectorTextSame('h3', 'Create legacy index template');

        $this->client->submitForm('Submit');

        $this->assertResponseStatusCodeSame(302);

        $this->client->followRedirect();
        $this->assertPageTitleSame('Legacy index templates - '.GENERATED_NAME.'-copy');
        $this->assertSelectorTextSame('h1', 'Legacy index templates');
        $this->assertSelectorTextSame('h2', GENERATED_NAME.'-copy');
        $this->assertSelectorTextSame('h3', 'Summary');
    }

    /**
     * @Route("/index-templates-legacy/{name}", name="index_templates_legacy_read")
     */
    public function testRead404(): void
    {
        $this->client->request('GET', '/admin/index-templates-legacy/'.uniqid());

        $this->assertResponseStatusCodeSame(404);
    }

    public function testRead(): void
    {
        $this->client->request('GET', '/admin/index-templates-legacy/'.GENERATED_NAME);

        $this->assertResponseStatusCodeSame(200);
        $this->assertPageTitleSame('Legacy index templates - '.GENERATED_NAME);
        $this->assertSelectorTextSame('h1', 'Legacy index templates');
        $this->assertSelectorTextSame('h2', GENERATED_NAME);
        $this->assertSelectorTextSame('h3', 'Summary');
    }

    /**
     * @Route("/index-templates-legacy/{name}/update", name="index_templates_legacy_update")
     */
    public function testUpdate404(): void
    {
        $this->client->request('GET', '/admin/index-templates-legacy/'.uniqid().'/update');

        $this->assertResponseStatusCodeSame(404);
    }

    public function testUpdate403(): void
    {
        $this->client->request('GET', '/admin/index-templates-legacy/'.GENERATED_NAME_SYSTEM.'/update');

        $this->assertResponseStatusCodeSame(403);
    }

    public function testUpdate(): void
    {
        $this->client->request('GET', '/admin/index-templates-legacy/'.GENERATED_NAME.'/update');

        $this->assertResponseStatusCodeSame(200);
        $this->assertPageTitleSame('Legacy index templates - '.GENERATED_NAME.' - Update');
        $this->assertSelectorTextSame('h1', 'Legacy index templates');
        $this->assertSelectorTextSame('h2', GENERATED_NAME);
        $this->assertSelectorTextSame('h3', 'Update');
    }

    /**
     * @Route("/index-templates-legacy/{name}/settings", name="index_templates_legacy_read_settings")
     */
    public function testSettings404(): void
    {
        $this->client->request('GET', '/admin/index-templates-legacy/'.uniqid().'/settings');

        $this->assertResponseStatusCodeSame(404);
    }

    public function testSettings(): void
    {
        $this->client->request('GET', '/admin/index-templates-legacy/'.GENERATED_NAME.'/settings');

        $this->assertResponseStatusCodeSame(200);
        $this->assertPageTitleSame('Legacy index templates - '.GENERATED_NAME.' - Settings');
        $this->assertSelectorTextSame('h1', 'Legacy index templates');
        $this->assertSelectorTextSame('h2', GENERATED_NAME);
        $this->assertSelectorTextContains('h3', 'Settings');
    }

    /**
     * @Route("/index-templates-legacy/{name}/mappings", name="index_templates_legacy_read_mappings")
     */
    public function testMappings404(): void
    {
        $this->client->request('GET', '/admin/index-templates-legacy/'.uniqid().'/mappings');

        $this->assertResponseStatusCodeSame(404);
    }

    public function testMappings(): void
    {
        $this->client->request('GET', '/admin/index-templates-legacy/'.GENERATED_NAME.'/mappings');

        $this->assertResponseStatusCodeSame(200);
        $this->assertPageTitleSame('Legacy index templates - '.GENERATED_NAME.' - Mappings');
        $this->assertSelectorTextSame('h1', 'Legacy index templates');
        $this->assertSelectorTextSame('h2', GENERATED_NAME);
        $this->assertSelectorTextSame('h3', 'Mappings');
    }

    /**
     * @Route("/index-templates-legacy/{name}/delete", name="index_templates_legacy_delete")
     */
    public function testDelete404(): void
    {
        $this->client->request('GET', '/admin/index-templates-legacy/'.uniqid().'/delete');

        $this->assertResponseStatusCodeSame(404);
    }

    public function testDelete403(): void
    {
        $this->client->request('GET', '/admin/index-templates-legacy/'.GENERATED_NAME_SYSTEM.'/delete');

        $this->assertResponseStatusCodeSame(403);

        $callRequest = new CallRequestModel();
        $callRequest->setMethod('DELETE');
        $callRequest->setPath('/_template/'.GENERATED_NAME_SYSTEM);
        $this->callManager->call($callRequest);
    }

    public function testDelete(): void
    {
        $this->client->request('GET', '/admin/index-templates-legacy/'.GENERATED_NAME.'/delete');

        $this->assertResponseStatusCodeSame(302);
    }

    public function testDeleteCopy(): void
    {
        $this->client->request('GET', '/admin/index-templates-legacy/'.GENERATED_NAME.'-copy/delete');

        $this->assertResponseStatusCodeSame(302);
    }
}
