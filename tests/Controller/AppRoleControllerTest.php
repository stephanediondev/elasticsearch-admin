<?php

namespace App\Tests\Controller;

use App\Tests\Controller\AbstractAppControllerTest;

class AppRoleControllerTest extends AbstractAppControllerTest
{
    public function testIndex(): void
    {
        $this->client->request('GET', '/admin/app-roles');

        $this->assertResponseStatusCodeSame(200);
        $this->assertPageTitleSame('Roles');
        $this->assertSelectorTextSame('h1', 'Roles');
        $this->assertSelectorTextContains('h3', 'List');
    }

    public function testCreate(): void
    {
        $this->client->request('GET', '/admin/app-roles/create');

        $this->assertResponseStatusCodeSame(200);
        $this->assertPageTitleSame('Roles - Create role');
        $this->assertSelectorTextSame('h1', 'Roles');
        $this->assertSelectorTextSame('h3', 'Create role');

        $values = [
            'data[name]' => GENERATED_NAME_UPPER,
        ];
        $this->client->submitForm('Submit', $values);

        $this->assertResponseStatusCodeSame(302);

        $this->client->followRedirect();
        $this->assertPageTitleSame('Roles - ROLE_'.GENERATED_NAME_UPPER.' - Update');
        $this->assertSelectorTextSame('h1', 'Roles');
        $this->assertSelectorTextSame('h2', 'ROLE_'.GENERATED_NAME_UPPER);
        $this->assertSelectorTextSame('h3', 'Update');
    }

    public function testRead404(): void
    {
        $this->client->request('GET', '/admin/app-roles/'.uniqid());

        $this->assertResponseStatusCodeSame(404);
    }

    public function testRead(): void
    {
        $this->client->request('GET', '/admin/app-roles/ROLE_'.GENERATED_NAME_UPPER);

        $this->assertResponseStatusCodeSame(200);
        $this->assertPageTitleSame('Roles - ROLE_'.GENERATED_NAME_UPPER);
        $this->assertSelectorTextSame('h1', 'Roles');
        $this->assertSelectorTextSame('h2', 'ROLE_'.GENERATED_NAME_UPPER);
        $this->assertSelectorTextSame('h3', 'Summary');
    }

    public function testUpdate404(): void
    {
        $this->client->request('GET', '/admin/app-roles/'.uniqid().'/update');

        $this->assertResponseStatusCodeSame(404);
    }

    public function testUpdate(): void
    {
        $this->client->request('GET', '/admin/app-roles/ROLE_'.GENERATED_NAME_UPPER.'/update');

        $this->assertResponseStatusCodeSame(200);
        $this->assertPageTitleSame('Roles - ROLE_'.GENERATED_NAME_UPPER.' - Update');
        $this->assertSelectorTextSame('h1', 'Roles');
        $this->assertSelectorTextSame('h2', 'ROLE_'.GENERATED_NAME_UPPER);
        $this->assertSelectorTextSame('h3', 'Update');
    }

    public function testDelete404(): void
    {
        $this->client->request('GET', '/admin/app-roles/'.uniqid().'/delete');

        $this->assertResponseStatusCodeSame(404);
    }

    public function testDelete(): void
    {
        $this->client->request('GET', '/admin/app-roles/ROLE_'.GENERATED_NAME_UPPER.'/delete');

        $this->assertResponseStatusCodeSame(302);
    }
}
