<?php

namespace App\Tests\Controller;

use App\Tests\Controller\AbstractAppControllerTest;

class ElasticsearchEnrichControllerTest extends AbstractAppControllerTest
{
    public function testIndex(): void
    {
        $this->client->request('GET', '/admin/enrich');

        if (false == $this->callManager->hasFeature('enrich')) {
            $this->assertResponseStatusCodeSame(403);
        } else {
            $this->assertResponseStatusCodeSame(200);
            $this->assertPageTitleSame('Enrich policies');
            $this->assertSelectorTextSame('h1', 'Enrich policies');
            $this->assertSelectorTextContains('h3', 'List');
        }
    }

    public function testStats(): void
    {
        $this->client->request('GET', '/admin/enrich/stats');

        if (false == $this->callManager->hasFeature('enrich')) {
            $this->assertResponseStatusCodeSame(403);
        } else {
            $this->assertResponseStatusCodeSame(200);
            $this->assertPageTitleSame('Enrich policies - Stats');
            $this->assertSelectorTextSame('h1', 'Enrich policies');
            $this->assertSelectorTextSame('h3', 'Stats');
        }
    }

    public function testCreate(): void
    {
        $this->client->request('GET', '/admin/enrich/create');

        if (false == $this->callManager->hasFeature('enrich')) {
            $this->assertResponseStatusCodeSame(403);
        } else {
            $this->assertResponseStatusCodeSame(200);
            $this->assertPageTitleSame('Enrich policies - Create enrich policy');
            $this->assertSelectorTextSame('h1', 'Enrich policies');
            $this->assertSelectorTextSame('h3', 'Create enrich policy');
        }
    }

    public function testCreateCopy404(): void
    {
        $this->client->request('GET', '/admin/enrich/create?policy='.uniqid());

        if (false == $this->callManager->hasFeature('enrich')) {
            $this->assertResponseStatusCodeSame(403);
        } else {
            $this->assertResponseStatusCodeSame(404);
        }
    }

    public function testCreateCopy(): void
    {
        $this->client->request('GET', '/admin/enrich/create?policy=elasticsearch-admin-test');

        if (false == $this->callManager->hasFeature('enrich')) {
            $this->assertResponseStatusCodeSame(403);
        } else {
            $this->assertResponseStatusCodeSame(200);
            $this->assertPageTitleSame('Enrich policies - Create enrich policy');
            $this->assertSelectorTextSame('h1', 'Enrich policies');
            $this->assertSelectorTextSame('h3', 'Create enrich policy');
        }
    }

    public function testRead404(): void
    {
        $this->client->request('GET', '/admin/enrich/'.uniqid());

        if (false == $this->callManager->hasFeature('enrich')) {
            $this->assertResponseStatusCodeSame(403);
        } else {
            $this->assertResponseStatusCodeSame(404);
        }
    }

    public function testRead(): void
    {
        $this->client->request('GET', '/admin/enrich/elasticsearch-admin-test');

        if (false == $this->callManager->hasFeature('enrich')) {
            $this->assertResponseStatusCodeSame(403);
        } else {
            $this->assertResponseStatusCodeSame(200);
            $this->assertPageTitleSame('Enrich policies - elasticsearch-admin-test');
            $this->assertSelectorTextSame('h1', 'Enrich policies');
            $this->assertSelectorTextSame('h2', 'elasticsearch-admin-test');
            $this->assertSelectorTextSame('h3', 'Summary');
        }
    }

    public function testDelete404(): void
    {
        $this->client->request('GET', '/admin/enrich/'.uniqid().'/delete');

        if (false == $this->callManager->hasFeature('enrich')) {
            $this->assertResponseStatusCodeSame(403);
        } else {
            $this->assertResponseStatusCodeSame(404);
        }
    }

    public function testDelete(): void
    {
        $this->client->request('GET', '/admin/enrich/elasticsearch-admin-test/delete');

        if (false == $this->callManager->hasFeature('enrich')) {
            $this->assertResponseStatusCodeSame(403);
        } else {
            $this->assertResponseStatusCodeSame(302);
        }
    }
}
