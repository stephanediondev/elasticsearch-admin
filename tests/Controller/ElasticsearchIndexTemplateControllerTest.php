<?php

namespace App\Tests\Controller;

/**
 * @Route("/admin")
 */
class ElasticsearchIndexTemplateControllerTest extends AbstractAppControllerTest
{
    /**
     * @Route("/index-templates", name="index_templates")
     */
    public function testIndex()
    {
        $this->client->request('GET', '/admin/index-templates');

        if (false == $this->callManager->hasFeature('composable_template')) {
            $this->assertResponseStatusCodeSame(403);
        } else {
            $this->assertResponseStatusCodeSame(200);
            $this->assertPageTitleSame('Composable index templates');
            $this->assertSelectorTextSame('h1', 'Composable index templates');
        }
    }

    /**
     * @Route("/index-templates/create", name="index_templates_create")
     */
    public function testCreate()
    {
        $this->client->request('GET', '/admin/index-templates/create');

        if (false == $this->callManager->hasFeature('composable_template')) {
            $this->assertResponseStatusCodeSame(403);
        } else {
            $this->assertResponseStatusCodeSame(200);
            $this->assertPageTitleSame('Composable index templates - Create composable index template');
            $this->assertSelectorTextSame('h1', 'Composable index templates');

            $values = [
                'data[name]' => GENERATED_NAME,
                'data[index_patterns]' => GENERATED_NAME,
            ];
            $this->client->submitForm('Submit', $values);

            $this->assertResponseStatusCodeSame(302);

            $this->client->followRedirect();
            $this->assertPageTitleSame('Composable index templates - '.GENERATED_NAME);
            $this->assertSelectorTextSame('h1', 'Composable index templates');
            $this->assertSelectorTextSame('h2', GENERATED_NAME);
        }
    }

    public function testCreateSystem()
    {
        $this->client->request('GET', '/admin/index-templates/create');

        if (false == $this->callManager->hasFeature('composable_template')) {
            $this->assertResponseStatusCodeSame(403);
        } else {
            $this->assertResponseStatusCodeSame(200);
            $this->assertPageTitleSame('Composable index templates - Create composable index template');
            $this->assertSelectorTextSame('h1', 'Composable index templates');

            $values = [
                'data[name]' => GENERATED_NAME_SYSTEM,
                'data[index_patterns]' => GENERATED_NAME_SYSTEM,
            ];
            $this->client->submitForm('Submit', $values);

            $this->assertResponseStatusCodeSame(302);

            $this->client->followRedirect();
            $this->assertPageTitleSame('Composable index templates - '.GENERATED_NAME_SYSTEM);
            $this->assertSelectorTextSame('h1', 'Composable index templates');
            $this->assertSelectorTextSame('h2', GENERATED_NAME_SYSTEM);
        }
    }

    public function testCreateCopy404()
    {
        $this->client->request('GET', '/admin/index-templates/create?template='.uniqid());

        if (false == $this->callManager->hasFeature('composable_template')) {
            $this->assertResponseStatusCodeSame(403);
        } else {
            $this->assertResponseStatusCodeSame(404);
        }
    }

    public function testCreateCopy403()
    {
        $this->client->request('GET', '/admin/index-templates/create?template='.GENERATED_NAME_SYSTEM);

        if (false == $this->callManager->hasFeature('composable_template')) {
            $this->assertResponseStatusCodeSame(403);
        } else {
            $this->assertResponseStatusCodeSame(403);
        }
    }

    public function testCreateCopy()
    {
        $this->client->request('GET', '/admin/index-templates/create?template='.GENERATED_NAME);

        if (false == $this->callManager->hasFeature('composable_template')) {
            $this->assertResponseStatusCodeSame(403);
        } else {
            $this->assertResponseStatusCodeSame(200);
            $this->assertPageTitleSame('Composable index templates - Create composable index template');
            $this->assertSelectorTextSame('h1', 'Composable index templates');

            $values = [
                'data[index_patterns]' => GENERATED_NAME.'-copy',
            ];
            $this->client->submitForm('Submit', $values);

            $this->assertResponseStatusCodeSame(302);

            $this->client->followRedirect();
            $this->assertPageTitleSame('Composable index templates - '.GENERATED_NAME.'-copy');
            $this->assertSelectorTextSame('h1', 'Composable index templates');
            $this->assertSelectorTextSame('h2', GENERATED_NAME.'-copy');
        }
    }

    /**
     * @Route("/index-templates/{name}", name="index_templates_read")
     */
    public function testRead404()
    {
        $this->client->request('GET', '/admin/index-templates/'.uniqid());

        if (false == $this->callManager->hasFeature('composable_template')) {
            $this->assertResponseStatusCodeSame(403);
        } else {
            $this->assertResponseStatusCodeSame(404);
        }
    }

    public function testRead()
    {
        $this->client->request('GET', '/admin/index-templates/'.GENERATED_NAME);

        if (false == $this->callManager->hasFeature('composable_template')) {
            $this->assertResponseStatusCodeSame(403);
        } else {
            $this->assertResponseStatusCodeSame(200);
            $this->assertPageTitleSame('Composable index templates - '.GENERATED_NAME);
            $this->assertSelectorTextSame('h1', 'Composable index templates');
        }
    }

    /**
     * @Route("/index-templates/{name}/update", name="index_templates_update")
     */
    public function testUpdate404()
    {
        $this->client->request('GET', '/admin/index-templates/'.uniqid().'/update');

        if (false == $this->callManager->hasFeature('composable_template')) {
            $this->assertResponseStatusCodeSame(403);
        } else {
            $this->assertResponseStatusCodeSame(404);
        }
    }

    public function testUpdate403()
    {
        $this->client->request('GET', '/admin/index-templates/'.GENERATED_NAME_SYSTEM.'/update');

        if (false == $this->callManager->hasFeature('composable_template')) {
            $this->assertResponseStatusCodeSame(403);
        } else {
            $this->assertResponseStatusCodeSame(403);
        }
    }

    public function testUpdate()
    {
        $this->client->request('GET', '/admin/index-templates/'.GENERATED_NAME.'/update');

        if (false == $this->callManager->hasFeature('composable_template')) {
            $this->assertResponseStatusCodeSame(403);
        } else {
            $this->assertResponseStatusCodeSame(200);
            $this->assertPageTitleSame('Composable index templates - '.GENERATED_NAME.' - Update');
            $this->assertSelectorTextSame('h1', 'Composable index templates');
            $this->assertSelectorTextSame('h2', GENERATED_NAME);
        }
    }

    /**
     * @Route("/index-templates/{name}/settings", name="index_templates_read_settings")
     */
    public function testSettings404()
    {
        $this->client->request('GET', '/admin/index-templates/'.uniqid().'/settings');

        if (false == $this->callManager->hasFeature('composable_template')) {
            $this->assertResponseStatusCodeSame(403);
        } else {
            $this->assertResponseStatusCodeSame(404);
        }
    }

    public function testSettings()
    {
        $this->client->request('GET', '/admin/index-templates/'.GENERATED_NAME.'/settings');

        if (false == $this->callManager->hasFeature('composable_template')) {
            $this->assertResponseStatusCodeSame(403);
        } else {
            $this->assertResponseStatusCodeSame(200);
            $this->assertPageTitleSame('Composable index templates - '.GENERATED_NAME.' - Settings');
            $this->assertSelectorTextSame('h1', 'Composable index templates');
            $this->assertSelectorTextSame('h2', GENERATED_NAME);
        }
    }

    /**
     * @Route("/index-templates/{name}/mappings", name="index_templates_read_mappings")
     */
    public function testMappings404()
    {
        $this->client->request('GET', '/admin/index-templates/'.uniqid().'/mappings');

        if (false == $this->callManager->hasFeature('composable_template')) {
            $this->assertResponseStatusCodeSame(403);
        } else {
            $this->assertResponseStatusCodeSame(404);
        }
    }

    public function testMappings()
    {
        $this->client->request('GET', '/admin/index-templates/'.GENERATED_NAME.'/mappings');

        if (false == $this->callManager->hasFeature('composable_template')) {
            $this->assertResponseStatusCodeSame(403);
        } else {
            $this->assertResponseStatusCodeSame(200);
            $this->assertPageTitleSame('Composable index templates - '.GENERATED_NAME.' - Mappings');
            $this->assertSelectorTextSame('h1', 'Composable index templates');
            $this->assertSelectorTextSame('h2', GENERATED_NAME);
        }
    }

    /**
     * @Route("/index-templates/{name}/simulate", name="index_templates_simulate")
     */
    public function testSimulate404()
    {
        $this->client->request('GET', '/admin/index-templates/'.uniqid().'/simulate');

        if (false == $this->callManager->hasFeature('composable_template')) {
            $this->assertResponseStatusCodeSame(403);
        } else {
            $this->assertResponseStatusCodeSame(404);
        }
    }

    public function testSimulate()
    {
        $this->client->request('GET', '/admin/index-templates/'.GENERATED_NAME.'/simulate');

        if (false == $this->callManager->hasFeature('composable_template')) {
            $this->assertResponseStatusCodeSame(403);
        } else {
            $this->assertResponseStatusCodeSame(200);
            $this->assertPageTitleSame('Composable index templates - '.GENERATED_NAME.' - Simulate');
            $this->assertSelectorTextSame('h1', 'Composable index templates');
            $this->assertSelectorTextSame('h2', GENERATED_NAME);
        }
    }

    /**
     * @Route("/index-templates/{name}/delete", name="index_templates_delete")
     */
    public function testDelete404()
    {
        $this->client->request('GET', '/admin/index-templates/'.uniqid().'/delete');

        if (false == $this->callManager->hasFeature('composable_template')) {
            $this->assertResponseStatusCodeSame(403);
        } else {
            $this->assertResponseStatusCodeSame(404);
        }
    }

    public function testDelete403()
    {
        $this->client->request('GET', '/admin/index-templates/'.GENERATED_NAME_SYSTEM.'/delete');

        if (false == $this->callManager->hasFeature('composable_template')) {
            $this->assertResponseStatusCodeSame(403);
        } else {
            $this->assertResponseStatusCodeSame(403);
        }
    }

    public function testDelete()
    {
        $this->client->request('GET', '/admin/index-templates/'.GENERATED_NAME.'/delete');

        if (false == $this->callManager->hasFeature('composable_template')) {
            $this->assertResponseStatusCodeSame(403);
        } else {
            $this->assertResponseStatusCodeSame(302);
        }
    }

    public function testDeleteCopy()
    {
        $this->client->request('GET', '/admin/index-templates/'.GENERATED_NAME.'-copy/delete');

        if (false == $this->callManager->hasFeature('composable_template')) {
            $this->assertResponseStatusCodeSame(403);
        } else {
            $this->assertResponseStatusCodeSame(302);
        }
    }
}
