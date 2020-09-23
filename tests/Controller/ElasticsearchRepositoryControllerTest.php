<?php

namespace App\Tests\Controller;

/**
 * @Route("/admin")
 */
class ElasticsearchRepositoryControllerTest extends AbstractAppControllerTest
{
    /**
     * @Route("/repositories", name="repositories")
     */
    public function testIndex()
    {
        $this->client->request('GET', '/admin/repositories');

        $this->assertResponseStatusCodeSame(200);
        $this->assertPageTitleSame('Repositories');
        $this->assertSelectorTextSame('h1', 'Repositories');
    }

    /**
     * @Route("/repositories/create/{type}", name="repositories_create")
     */
    public function testCreate403()
    {
        $this->client->request('GET', '/admin/repositories/create/'.uniqid());

        $this->assertResponseStatusCodeSame(403);
    }

    public function testCreateFs()
    {
        $this->client->request('GET', '/admin/repositories/create/fs');

        $this->assertResponseStatusCodeSame(200);
        $this->assertPageTitleSame('Repositories - Create Shared file system repository');
        $this->assertSelectorTextSame('h1', 'Repositories');
        $this->assertSelectorTextSame('h3', 'Create Shared file system repository');
    }

    public function testCreateS3()
    {
        $this->client->request('GET', '/admin/repositories/create/s3');

        if (false == $this->callManager->hasPlugin('repository-s3')) {
            $this->assertResponseStatusCodeSame(403);
        } else {
            $this->assertResponseStatusCodeSame(200);
            $this->assertPageTitleSame('Repositories - Create AWS S3 repository');
            $this->assertSelectorTextSame('h1', 'Repositories');
            $this->assertSelectorTextSame('h3', 'Create AWS S3 repository');
        }
    }

    public function testCreateGcs()
    {
        $this->client->request('GET', '/admin/repositories/create/gcs');

        if (false == $this->callManager->hasPlugin('repository-gcs')) {
            $this->assertResponseStatusCodeSame(403);
        } else {
            $this->assertResponseStatusCodeSame(200);
            $this->assertPageTitleSame('Repositories - Create Google Cloud Storage repository');
            $this->assertSelectorTextSame('h1', 'Repositories');
            $this->assertSelectorTextSame('h3', 'Create Google Cloud Storage repository');
        }
    }

    public function testCreateAzure()
    {
        $this->client->request('GET', '/admin/repositories/create/azure');

        if (false == $this->callManager->hasPlugin('repository-azure')) {
            $this->assertResponseStatusCodeSame(403);
        } else {
            $this->assertResponseStatusCodeSame(200);
            $this->assertPageTitleSame('Repositories - Create Microsoft Azure repository');
            $this->assertSelectorTextSame('h1', 'Repositories');
            $this->assertSelectorTextSame('h3', 'Create Microsoft Azure repository');
        }
    }

    /**
     * @Route("/repositories/{repository}", name="repositories_read")
     */
    public function testRead404()
    {
        $this->client->request('GET', '/admin/repositories/'.uniqid());

        $this->assertResponseStatusCodeSame(404);
    }

    /**
     * @Route("/repositories/{repository}/update", name="repositories_update")
     */
    public function testUpdate404()
    {
        $this->client->request('GET', '/admin/repositories/'.uniqid().'/update');

        $this->assertResponseStatusCodeSame(404);
    }
}
