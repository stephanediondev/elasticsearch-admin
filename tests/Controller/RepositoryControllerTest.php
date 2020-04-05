<?php

namespace App\Tests\Controller;

use App\Tests\Controller;

class RepositoryControllerTest extends AbstractAppControllerTest
{
    public function testIndex()
    {
        $this->client->request('GET', '/admin/repositories');

        $this->assertResponseStatusCodeSame(200);
    }

    public function testCreateFs()
    {
        $this->client->request('GET', '/admin/repositories/create/fs');

        $this->assertResponseStatusCodeSame(200);
    }

    public function testCreateS3()
    {
        $this->client->request('GET', '/admin/repositories/create/s3');

        $this->assertResponseStatusCodeSame(200);
    }

    public function testCreateGcs()
    {
        $this->client->request('GET', '/admin/repositories/create/gcs');

        $this->assertResponseStatusCodeSame(200);
    }
}
