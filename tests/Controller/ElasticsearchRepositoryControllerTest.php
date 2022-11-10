<?php

namespace App\Tests\Controller;

use App\Tests\Controller\AbstractAppControllerTest;

class ElasticsearchRepositoryControllerTest extends AbstractAppControllerTest
{
    public function testIndex(): void
    {
        $this->client->request('GET', '/admin/repositories');

        $this->assertResponseStatusCodeSame(200);
        $this->assertPageTitleSame('Repositories');
        $this->assertSelectorTextSame('h1', 'Repositories');
        $this->assertSelectorTextContains('h3', 'List');
    }

    public function testCreate403(): void
    {
        $this->client->request('GET', '/admin/repositories/create/'.uniqid());

        $this->assertResponseStatusCodeSame(403);
    }

    public function testCreateFs(): void
    {
        $this->client->request('GET', '/admin/repositories/create/fs');

        $this->assertResponseStatusCodeSame(200);
        $this->assertPageTitleSame('Repositories - Create Shared file system repository');
        $this->assertSelectorTextSame('h1', 'Repositories');
        $this->assertSelectorTextSame('h3', 'Create Shared file system repository');
    }

    public function testCreateS3(): void
    {
        $this->client->request('GET', '/admin/repositories/create/s3');

        if (false == $this->callManager->hasPlugin('repository-s3') && false === $this->callManager->hasFeature('repository_plugins_to_modules')) {
            $this->assertResponseStatusCodeSame(403);
        } else {
            $this->assertResponseStatusCodeSame(200);
            $this->assertPageTitleSame('Repositories - Create AWS S3 repository');
            $this->assertSelectorTextSame('h1', 'Repositories');
            $this->assertSelectorTextSame('h3', 'Create AWS S3 repository');
        }
    }

    public function testCreateGcs(): void
    {
        $this->client->request('GET', '/admin/repositories/create/gcs');

        if (false == $this->callManager->hasPlugin('repository-gcs') && false === $this->callManager->hasFeature('repository_plugins_to_modules')) {
            $this->assertResponseStatusCodeSame(403);
        } else {
            $this->assertResponseStatusCodeSame(200);
            $this->assertPageTitleSame('Repositories - Create Google Cloud Storage repository');
            $this->assertSelectorTextSame('h1', 'Repositories');
            $this->assertSelectorTextSame('h3', 'Create Google Cloud Storage repository');
        }
    }

    public function testCreateAzure(): void
    {
        $this->client->request('GET', '/admin/repositories/create/azure');

        if (false == $this->callManager->hasPlugin('repository-azure') && false === $this->callManager->hasFeature('repository_plugins_to_modules')) {
            $this->assertResponseStatusCodeSame(403);
        } else {
            $this->assertResponseStatusCodeSame(200);
            $this->assertPageTitleSame('Repositories - Create Microsoft Azure repository');
            $this->assertSelectorTextSame('h1', 'Repositories');
            $this->assertSelectorTextSame('h3', 'Create Microsoft Azure repository');
        }
    }

    public function testRead404(): void
    {
        $this->client->request('GET', '/admin/repositories/'.uniqid());

        $this->assertResponseStatusCodeSame(404);
    }

    public function testUpdate404(): void
    {
        $this->client->request('GET', '/admin/repositories/'.uniqid().'/update');

        $this->assertResponseStatusCodeSame(404);
    }
}
