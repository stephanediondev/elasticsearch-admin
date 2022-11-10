<?php

namespace App\Tests\Controller;

use App\Tests\Controller\AbstractAppControllerTest;

use App\Model\CallRequestModel;

class ElasticsearchIndexControllerTest extends AbstractAppControllerTest
{
    public function testIndex(): void
    {
        $this->client->request('GET', '/admin/indices');

        $this->assertResponseStatusCodeSame(200);
        $this->assertPageTitleSame('Indices');
        $this->assertSelectorTextSame('h1', 'Indices');
        $this->assertSelectorTextContains('h3', 'List');
    }

    public function testStats(): void
    {
        $this->client->request('GET', '/admin/indices/stats');

        $this->assertResponseStatusCodeSame(200);
        $this->assertPageTitleSame('Indices - Stats');
        $this->assertSelectorTextSame('h1', 'Indices');
        $this->assertSelectorTextSame('h3', 'Stats');
    }

    public function testReindex(): void
    {
        $this->client->request('GET', '/admin/indices/reindex');

        $this->assertResponseStatusCodeSame(200);
        $this->assertPageTitleSame('Indices - Reindex');
        $this->assertSelectorTextSame('h1', 'Indices');
        $this->assertSelectorTextSame('h3', 'Reindex');
    }

    public function testCreate(): void
    {
        $this->client->request('GET', '/admin/indices/create');

        $this->assertResponseStatusCodeSame(200);
        $this->assertPageTitleSame('Indices - Create index');
        $this->assertSelectorTextSame('h1', 'Indices');
        $this->assertSelectorTextSame('h3', 'Create index');

        $values = [
            'data[name]' => GENERATED_NAME,
        ];
        if (true === $this->callManager->checkVersion('7.0')) {
            $values['data[mappings_json]'] = file_get_contents(__DIR__.'/../../src/DataFixtures/es-test-mappings.json');
        }
        $this->client->submitForm('Submit', $values);

        $this->assertResponseStatusCodeSame(302);

        $this->client->followRedirect();
        $this->assertPageTitleSame('Indices - '.GENERATED_NAME);
        $this->assertSelectorTextSame('h1', 'Indices');
        $this->assertSelectorTextContains('h2', GENERATED_NAME);
        $this->assertSelectorTextSame('h3', 'Summary');
    }

    public function testCreateSystem(): void
    {
        $this->client->request('GET', '/admin/indices/create');

        $this->assertResponseStatusCodeSame(200);
        $this->assertPageTitleSame('Indices - Create index');
        $this->assertSelectorTextSame('h1', 'Indices');
        $this->assertSelectorTextSame('h3', 'Create index');

        $values = [
            'data[name]' => GENERATED_NAME_SYSTEM,
        ];
        if (true === $this->callManager->checkVersion('7.0')) {
            $values['data[mappings_json]'] = file_get_contents(__DIR__.'/../../src/DataFixtures/es-test-mappings.json');
        }
        $this->client->submitForm('Submit', $values);

        $this->assertResponseStatusCodeSame(302);

        $this->client->followRedirect();
        $this->assertPageTitleSame('Indices - '.GENERATED_NAME_SYSTEM);
        $this->assertSelectorTextSame('h1', 'Indices');
        $this->assertSelectorTextContains('h2', GENERATED_NAME_SYSTEM);
        $this->assertSelectorTextSame('h3', 'Summary');
    }

    public function testRead404(): void
    {
        $this->client->request('GET', '/admin/indices/'.uniqid());

        $this->assertResponseStatusCodeSame(404);
    }

    public function testRead(): void
    {
        $this->client->request('GET', '/admin/indices/'.GENERATED_NAME);

        $this->assertResponseStatusCodeSame(200);
        $this->assertPageTitleSame('Indices - '.GENERATED_NAME);
        $this->assertSelectorTextSame('h1', 'Indices');
        $this->assertSelectorTextContains('h2', GENERATED_NAME);
        $this->assertSelectorTextSame('h3', 'Summary');
    }

    public function testUpdate404(): void
    {
        $this->client->request('GET', '/admin/indices/'.uniqid().'/update');

        $this->assertResponseStatusCodeSame(404);
    }

    public function testUpdate403(): void
    {
        $this->client->request('GET', '/admin/indices/'.GENERATED_NAME_SYSTEM.'/update');

        $this->assertResponseStatusCodeSame(403);
    }

    public function testUpdate(): void
    {
        $this->client->request('GET', '/admin/indices/'.GENERATED_NAME.'/update');

        $this->assertResponseStatusCodeSame(200);
        $this->assertPageTitleSame('Indices - '.GENERATED_NAME.' - Update');
        $this->assertSelectorTextSame('h1', 'Indices');
        $this->assertSelectorTextContains('h2', GENERATED_NAME);
        $this->assertSelectorTextSame('h3', 'Update');
    }

    public function testSettings404(): void
    {
        $this->client->request('GET', '/admin/indices/'.uniqid().'/settings');

        $this->assertResponseStatusCodeSame(404);
    }

    public function testSettings(): void
    {
        $this->client->request('GET', '/admin/indices/'.GENERATED_NAME.'/settings');

        $this->assertResponseStatusCodeSame(200);
        $this->assertPageTitleSame('Indices - '.GENERATED_NAME.' - Settings');
        $this->assertSelectorTextSame('h1', 'Indices');
        $this->assertSelectorTextContains('h3', 'Settings');
    }

    public function testMappings404(): void
    {
        $this->client->request('GET', '/admin/indices/'.uniqid().'/mappings');

        $this->assertResponseStatusCodeSame(404);
    }

    public function testMappings(): void
    {
        $this->client->request('GET', '/admin/indices/'.GENERATED_NAME.'/mappings');

        $this->assertResponseStatusCodeSame(200);
        $this->assertPageTitleSame('Indices - '.GENERATED_NAME.' - Mappings');
        $this->assertSelectorTextSame('h1', 'Indices');
        $this->assertSelectorTextSame('h3', 'Mappings');
    }

    public function testLifecycle404(): void
    {
        $this->client->request('GET', '/admin/indices/'.uniqid().'/lifecycle');

        if (false == $this->callManager->hasFeature('ilm')) {
            $this->assertResponseStatusCodeSame(403);
        } else {
            $this->assertResponseStatusCodeSame(404);
        }
    }

    public function testLifecycle(): void
    {
        $this->client->request('GET', '/admin/indices/'.GENERATED_NAME.'/lifecycle');

        if (false == $this->callManager->hasFeature('ilm')) {
            $this->assertResponseStatusCodeSame(403);
        } else {
            $this->assertResponseStatusCodeSame(200);
            $this->assertPageTitleSame('Indices - '.GENERATED_NAME.' - Lifecycle');
            $this->assertSelectorTextSame('h1', 'Indices');
            $this->assertSelectorTextSame('h3', 'Lifecycle');
        }
    }

    public function testSearch404(): void
    {
        $this->client->request('GET', '/admin/indices/'.uniqid().'/search');

        $this->assertResponseStatusCodeSame(404);
    }

    public function testSearch403(): void
    {
        $this->client->request('GET', '/admin/indices/'.GENERATED_NAME_SYSTEM.'/search');

        $this->assertResponseStatusCodeSame(403);
    }

    public function testSearch(): void
    {
        $this->client->request('GET', '/admin/indices/'.GENERATED_NAME.'/search');

        $this->assertResponseStatusCodeSame(200);
        $this->assertPageTitleSame('Indices - '.GENERATED_NAME.' - Search');
        $this->assertSelectorTextSame('h1', 'Indices');
        $this->assertSelectorTextContains('h3', 'Documents');
    }

    public function testImport404(): void
    {
        $this->client->request('GET', '/admin/indices/'.uniqid().'/file-import');

        $this->assertResponseStatusCodeSame(404);
    }

    public function testImport403(): void
    {
        $this->client->request('GET', '/admin/indices/'.GENERATED_NAME_SYSTEM.'/file-import');

        $this->assertResponseStatusCodeSame(403);
    }

    public function testImport(): void
    {
        $this->client->request('GET', '/admin/indices/'.GENERATED_NAME.'/file-import');

        $this->assertResponseStatusCodeSame(200);
        $this->assertPageTitleSame('Indices - '.GENERATED_NAME.' - Import from file');
        $this->assertSelectorTextSame('h1', 'Indices');
        $this->assertSelectorTextSame('h3', 'Import from file');
    }

    public function testAliases404(): void
    {
        $this->client->request('GET', '/admin/indices/'.uniqid().'/aliases');

        $this->assertResponseStatusCodeSame(404);
    }

    public function testAliases(): void
    {
        $this->client->request('GET', '/admin/indices/'.GENERATED_NAME.'/aliases');

        $this->assertResponseStatusCodeSame(200);
        $this->assertPageTitleSame('Indices - '.GENERATED_NAME.' - Aliases');
        $this->assertSelectorTextSame('h1', 'Indices');
    }

    public function testCreateAlias404(): void
    {
        $this->client->request('GET', '/admin/indices/'.uniqid().'/aliases/create');

        $this->assertResponseStatusCodeSame(404);
    }

    public function testCreateAlias(): void
    {
        $this->client->request('GET', '/admin/indices/'.GENERATED_NAME.'/aliases/create');

        $this->assertResponseStatusCodeSame(200);
        $this->assertPageTitleSame('Indices - '.GENERATED_NAME.' - Aliases');
        $this->assertSelectorTextSame('h1', 'Indices');
        $this->assertSelectorTextSame('h3', 'Add');
    }

    public function testRefresh404(): void
    {
        $this->client->request('GET', '/admin/indices/'.uniqid().'/refresh');

        $this->assertResponseStatusCodeSame(404);
    }

    public function testRefresh403(): void
    {
        $this->client->request('GET', '/admin/indices/'.GENERATED_NAME_SYSTEM.'/refresh');

        $this->assertResponseStatusCodeSame(403);
    }

    public function testRefresh(): void
    {
        $this->client->request('GET', '/admin/indices/'.GENERATED_NAME.'/refresh');

        $this->assertResponseStatusCodeSame(302);
    }

    public function testEmpty404(): void
    {
        $this->client->request('GET', '/admin/indices/'.uniqid().'/empty');

        if (false == $this->callManager->hasFeature('delete_by_query')) {
            $this->assertResponseStatusCodeSame(403);
        } else {
            $this->assertResponseStatusCodeSame(404);
        }
    }

    public function testEmpty403(): void
    {
        $this->client->request('GET', '/admin/indices/'.GENERATED_NAME_SYSTEM.'/empty');

        if (false == $this->callManager->hasFeature('delete_by_query')) {
            $this->assertResponseStatusCodeSame(403);
        } else {
            $this->assertResponseStatusCodeSame(403);
        }
    }

    public function testEmpty(): void
    {
        $this->client->request('GET', '/admin/indices/'.GENERATED_NAME.'/empty');

        if (false == $this->callManager->hasFeature('delete_by_query')) {
            $this->assertResponseStatusCodeSame(403);
        } else {
            $this->assertResponseStatusCodeSame(302);
        }
    }

    public function testClose404(): void
    {
        $this->client->request('GET', '/admin/indices/'.uniqid().'/close');

        $this->assertResponseStatusCodeSame(404);
    }

    public function testClose403(): void
    {
        $this->client->request('GET', '/admin/indices/'.GENERATED_NAME_SYSTEM.'/close');

        $this->assertResponseStatusCodeSame(403);
    }

    public function testClose(): void
    {
        $this->client->request('GET', '/admin/indices/'.GENERATED_NAME.'/close');

        $clusterSettings = $this->elasticsearchClusterManager->getClusterSettings();

        if (true == isset($clusterSettings['cluster.indices.close.enable']) && 'false' == $clusterSettings['cluster.indices.close.enable']) {
            $this->assertResponseStatusCodeSame(403);
        } else {
            $this->assertResponseStatusCodeSame(302);
        }
    }

    public function testOpen404(): void
    {
        $this->client->request('GET', '/admin/indices/'.uniqid().'/open');

        $this->assertResponseStatusCodeSame(404);
    }

    public function testOpen403(): void
    {
        $this->client->request('GET', '/admin/indices/'.GENERATED_NAME_SYSTEM.'/open');

        $this->assertResponseStatusCodeSame(403);
    }

    public function testOpen(): void
    {
        $this->client->request('GET', '/admin/indices/'.GENERATED_NAME.'/open');

        $clusterSettings = $this->elasticsearchClusterManager->getClusterSettings();

        if (true == isset($clusterSettings['cluster.indices.close.enable']) && 'false' == $clusterSettings['cluster.indices.close.enable']) {
            $this->assertResponseStatusCodeSame(403);
        } else {
            $this->assertResponseStatusCodeSame(302);
        }
    }

    public function testFreeze404(): void
    {
        $this->client->request('GET', '/admin/indices/'.uniqid().'/freeze');

        if (true === $this->callManager->hasFeature('freezing_endpoint_removed')) {
            $this->assertResponseStatusCodeSame(403);
        } elseif (false == $this->callManager->hasFeature('freeze_unfreeze')) {
            $this->assertResponseStatusCodeSame(403);
        } else {
            $this->assertResponseStatusCodeSame(404);
        }
    }

    public function testFreeze403(): void
    {
        $this->client->request('GET', '/admin/indices/'.GENERATED_NAME_SYSTEM.'/freeze');

        if (true === $this->callManager->hasFeature('freezing_endpoint_removed')) {
            $this->assertResponseStatusCodeSame(403);
        } elseif (false == $this->callManager->hasFeature('freeze_unfreeze')) {
            $this->assertResponseStatusCodeSame(403);
        } else {
            $this->assertResponseStatusCodeSame(403);
        }
    }

    public function testFreeze(): void
    {
        $this->client->request('GET', '/admin/indices/'.GENERATED_NAME.'/freeze');

        if (true === $this->callManager->hasFeature('freezing_endpoint_removed')) {
            $this->assertResponseStatusCodeSame(403);
        } elseif (false == $this->callManager->hasFeature('freeze_unfreeze')) {
            $this->assertResponseStatusCodeSame(403);
        } else {
            $this->assertResponseStatusCodeSame(302);
        }
    }

    public function testUnfreeze404(): void
    {
        $this->client->request('GET', '/admin/indices/'.uniqid().'/unfreeze');

        if (true === $this->callManager->hasFeature('freezing_endpoint_removed')) {
            $this->assertResponseStatusCodeSame(403);
        } elseif (false == $this->callManager->hasFeature('freeze_unfreeze')) {
            $this->assertResponseStatusCodeSame(403);
        } else {
            $this->assertResponseStatusCodeSame(404);
        }
    }

    public function testUnfreeze403(): void
    {
        $this->client->request('GET', '/admin/indices/'.GENERATED_NAME_SYSTEM.'/unfreeze');

        if (true === $this->callManager->hasFeature('freezing_endpoint_removed')) {
            $this->assertResponseStatusCodeSame(403);
        } elseif (false == $this->callManager->hasFeature('freeze_unfreeze')) {
            $this->assertResponseStatusCodeSame(403);
        } else {
            $this->assertResponseStatusCodeSame(403);
        }
    }

    public function testUnfreeze(): void
    {
        $this->client->request('GET', '/admin/indices/'.GENERATED_NAME.'/unfreeze');

        if (true === $this->callManager->hasFeature('freezing_endpoint_removed')) {
            $this->assertResponseStatusCodeSame(403);
        } elseif (false == $this->callManager->hasFeature('freeze_unfreeze')) {
            $this->assertResponseStatusCodeSame(403);
        } else {
            $this->assertResponseStatusCodeSame(302);
        }
    }

    public function testDelete404(): void
    {
        $this->client->request('GET', '/admin/indices/'.uniqid().'/delete');

        $this->assertResponseStatusCodeSame(404);
    }

    public function testDelete403(): void
    {
        $this->client->request('GET', '/admin/indices/'.GENERATED_NAME_SYSTEM.'/delete');

        $this->assertResponseStatusCodeSame(403);

        $callRequest = new CallRequestModel();
        $callRequest->setMethod('DELETE');
        $callRequest->setPath('/'.GENERATED_NAME_SYSTEM);
        $this->callManager->call($callRequest);
    }

    public function testDelete(): void
    {
        $this->client->request('GET', '/admin/indices/'.GENERATED_NAME.'/delete');

        $this->assertResponseStatusCodeSame(302);
    }
}
