<?php

namespace App\Tests\Controller;

use App\Tests\Controller\AbstractAppControllerTest;

use App\Model\CallRequestModel;

/**
 * @Route("/admin")
 */
class ElasticsearchComponentTemplateControllerTest extends AbstractAppControllerTest
{
    /**
     * @Route("/component-templates", name="component_templates")
     */
    public function testComponent(): void
    {
        $this->client->request('GET', '/admin/component-templates');

        if (false == $this->callManager->hasFeature('composable_template')) {
            $this->assertResponseStatusCodeSame(403);
        } else {
            $this->assertResponseStatusCodeSame(200);
            $this->assertPageTitleSame('Component templates');
            $this->assertSelectorTextSame('h1', 'Component templates');
            $this->assertSelectorTextContains('h3', 'List');
        }
    }

    /**
     * @Route("/component-templates/create", name="component_templates_create")
     */
    public function testCreate(): void
    {
        $this->client->request('GET', '/admin/component-templates/create');

        if (false == $this->callManager->hasFeature('composable_template')) {
            $this->assertResponseStatusCodeSame(403);
        } else {
            $this->assertResponseStatusCodeSame(200);
            $this->assertPageTitleSame('Component templates - Create component template');
            $this->assertSelectorTextSame('h1', 'Component templates');
            $this->assertSelectorTextSame('h3', 'Create component template');

            $values = [
                'data[name]' => GENERATED_NAME,
            ];
            $this->client->submitForm('Submit', $values);

            $this->assertResponseStatusCodeSame(302);

            $this->client->followRedirect();
            $this->assertPageTitleSame('Component templates - '.GENERATED_NAME);
            $this->assertSelectorTextSame('h1', 'Component templates');
            $this->assertSelectorTextSame('h2', GENERATED_NAME);
            $this->assertSelectorTextSame('h3', 'Summary');
        }
    }

    public function testCreateSystem(): void
    {
        $this->client->request('GET', '/admin/component-templates/create');

        if (false == $this->callManager->hasFeature('composable_template')) {
            $this->assertResponseStatusCodeSame(403);
        } else {
            $this->assertResponseStatusCodeSame(200);
            $this->assertPageTitleSame('Component templates - Create component template');
            $this->assertSelectorTextSame('h1', 'Component templates');
            $this->assertSelectorTextSame('h3', 'Create component template');

            $values = [
                'data[name]' => GENERATED_NAME_SYSTEM,
            ];
            $this->client->submitForm('Submit', $values);

            $this->assertResponseStatusCodeSame(302);

            $this->client->followRedirect();
            $this->assertPageTitleSame('Component templates - '.GENERATED_NAME_SYSTEM);
            $this->assertSelectorTextSame('h1', 'Component templates');
            $this->assertSelectorTextSame('h2', GENERATED_NAME_SYSTEM);
            $this->assertSelectorTextSame('h3', 'Summary');
        }
    }

    public function testCreateCopy404(): void
    {
        $this->client->request('GET', '/admin/component-templates/create?template='.uniqid());

        if (false == $this->callManager->hasFeature('composable_template')) {
            $this->assertResponseStatusCodeSame(403);
        } else {
            $this->assertResponseStatusCodeSame(404);
        }
    }

    public function testCreateCopy403(): void
    {
        $this->client->request('GET', '/admin/component-templates/create?template='.GENERATED_NAME_SYSTEM);

        if (false == $this->callManager->hasFeature('composable_template')) {
            $this->assertResponseStatusCodeSame(403);
        } else {
            $this->assertResponseStatusCodeSame(403);
        }
    }

    public function testCreateCopy(): void
    {
        $this->client->request('GET', '/admin/component-templates/create?template='.GENERATED_NAME);

        if (false == $this->callManager->hasFeature('composable_template')) {
            $this->assertResponseStatusCodeSame(403);
        } else {
            $this->assertResponseStatusCodeSame(200);
            $this->assertPageTitleSame('Component templates - Create component template');
            $this->assertSelectorTextSame('h1', 'Component templates');
            $this->assertSelectorTextSame('h3', 'Create component template');

            $this->client->submitForm('Submit');

            $this->assertResponseStatusCodeSame(302);

            $this->client->followRedirect();
            $this->assertPageTitleSame('Component templates - '.GENERATED_NAME.'-copy');
            $this->assertSelectorTextSame('h1', 'Component templates');
            $this->assertSelectorTextSame('h2', GENERATED_NAME.'-copy');
            $this->assertSelectorTextSame('h3', 'Summary');
        }
    }

    /**
     * @Route("/component-templates/{name}", name="component_templates_read")
     */
    public function testRead404(): void
    {
        $this->client->request('GET', '/admin/component-templates/'.uniqid());

        if (false == $this->callManager->hasFeature('composable_template')) {
            $this->assertResponseStatusCodeSame(403);
        } else {
            $this->assertResponseStatusCodeSame(404);
        }
    }

    public function testRead(): void
    {
        $this->client->request('GET', '/admin/component-templates/'.GENERATED_NAME);

        if (false == $this->callManager->hasFeature('composable_template')) {
            $this->assertResponseStatusCodeSame(403);
        } else {
            $this->assertResponseStatusCodeSame(200);
            $this->assertPageTitleSame('Component templates - '.GENERATED_NAME);
            $this->assertSelectorTextSame('h1', 'Component templates');
            $this->assertSelectorTextSame('h2', GENERATED_NAME);
            $this->assertSelectorTextSame('h3', 'Summary');
        }
    }

    /**
     * @Route("/component-templates/{name}/update", name="component_templates_update")
     */
    public function testUpdate404(): void
    {
        $this->client->request('GET', '/admin/component-templates/'.uniqid().'/update');

        if (false == $this->callManager->hasFeature('composable_template')) {
            $this->assertResponseStatusCodeSame(403);
        } else {
            $this->assertResponseStatusCodeSame(404);
        }
    }

    public function testUpdate403(): void
    {
        $this->client->request('GET', '/admin/component-templates/'.GENERATED_NAME_SYSTEM.'/update');

        if (false == $this->callManager->hasFeature('composable_template')) {
            $this->assertResponseStatusCodeSame(403);
        } else {
            $this->assertResponseStatusCodeSame(403);
        }
    }

    public function testUpdate(): void
    {
        $this->client->request('GET', '/admin/component-templates/'.GENERATED_NAME.'/update');

        if (false == $this->callManager->hasFeature('composable_template')) {
            $this->assertResponseStatusCodeSame(403);
        } else {
            $this->assertResponseStatusCodeSame(200);
            $this->assertPageTitleSame('Component templates - '.GENERATED_NAME.' - Update');
            $this->assertSelectorTextSame('h1', 'Component templates');
            $this->assertSelectorTextSame('h2', GENERATED_NAME);
            $this->assertSelectorTextSame('h3', 'Update');
        }
    }

    /**
     * @Route("/component-templates/{name}/settings", name="component_templates_read_settings")
     */
    public function testSettings404(): void
    {
        $this->client->request('GET', '/admin/component-templates/'.uniqid().'/settings');

        if (false == $this->callManager->hasFeature('composable_template')) {
            $this->assertResponseStatusCodeSame(403);
        } else {
            $this->assertResponseStatusCodeSame(404);
        }
    }

    public function testSettings(): void
    {
        $this->client->request('GET', '/admin/component-templates/'.GENERATED_NAME.'/settings');

        if (false == $this->callManager->hasFeature('composable_template')) {
            $this->assertResponseStatusCodeSame(403);
        } else {
            $this->assertResponseStatusCodeSame(200);
            $this->assertPageTitleSame('Component templates - '.GENERATED_NAME.' - Settings');
            $this->assertSelectorTextSame('h1', 'Component templates');
            $this->assertSelectorTextSame('h2', GENERATED_NAME);
            $this->assertSelectorTextSame('h3', 'Settings');
        }
    }

    /**
     * @Route("/component-templates/{name}/mappings", name="component_templates_read_mappings")
     */
    public function testMappings404(): void
    {
        $this->client->request('GET', '/admin/component-templates/'.uniqid().'/mappings');

        if (false == $this->callManager->hasFeature('composable_template')) {
            $this->assertResponseStatusCodeSame(403);
        } else {
            $this->assertResponseStatusCodeSame(404);
        }
    }

    public function testMappings(): void
    {
        $this->client->request('GET', '/admin/component-templates/'.GENERATED_NAME.'/mappings');

        if (false == $this->callManager->hasFeature('composable_template')) {
            $this->assertResponseStatusCodeSame(403);
        } else {
            $this->assertResponseStatusCodeSame(200);
            $this->assertPageTitleSame('Component templates - '.GENERATED_NAME.' - Mappings');
            $this->assertSelectorTextSame('h1', 'Component templates');
            $this->assertSelectorTextSame('h2', GENERATED_NAME);
            $this->assertSelectorTextSame('h3', 'Mappings');
        }
    }

    /**
     * @Route("/component-templates/{name}/delete", name="component_templates_delete")
     */
    public function testDelete404(): void
    {
        $this->client->request('GET', '/admin/component-templates/'.uniqid().'/delete');

        if (false == $this->callManager->hasFeature('composable_template')) {
            $this->assertResponseStatusCodeSame(403);
        } else {
            $this->assertResponseStatusCodeSame(404);
        }
    }

    public function testDelete403(): void
    {
        $this->client->request('GET', '/admin/component-templates/'.GENERATED_NAME_SYSTEM.'/delete');

        if (false == $this->callManager->hasFeature('composable_template')) {
            $this->assertResponseStatusCodeSame(403);
        } else {
            $this->assertResponseStatusCodeSame(403);

            $callRequest = new CallRequestModel();
            $callRequest->setMethod('DELETE');
            $callRequest->setPath('/_component_template/'.GENERATED_NAME_SYSTEM);
            $this->callManager->call($callRequest);
        }
    }

    public function testDelete(): void
    {
        $this->client->request('GET', '/admin/component-templates/'.GENERATED_NAME.'/delete');

        if (false == $this->callManager->hasFeature('composable_template')) {
            $this->assertResponseStatusCodeSame(403);
        } else {
            $this->assertResponseStatusCodeSame(302);
        }
    }

    public function testDeleteCopy(): void
    {
        $this->client->request('GET', '/admin/component-templates/'.GENERATED_NAME.'-copy/delete');

        if (false == $this->callManager->hasFeature('composable_template')) {
            $this->assertResponseStatusCodeSame(403);
        } else {
            $this->assertResponseStatusCodeSame(302);
        }
    }
}
