<?php

namespace App\Tests\Controller;

use App\Tests\Controller\AbstractAppControllerTest;

class AppUserControllerTest extends AbstractAppControllerTest
{
    public function testIndex(): void
    {
        $this->client->request('GET', '/admin/app-users');

        $this->assertResponseStatusCodeSame(200);
        $this->assertPageTitleSame('Users');
        $this->assertSelectorTextSame('h1', 'Users');
        $this->assertSelectorTextContains('h3', 'List');
    }

    public function testCreate(): void
    {
        $this->client->request('GET', '/admin/app-users/create');

        $this->assertResponseStatusCodeSame(200);
        $this->assertPageTitleSame('Users - Create user');
        $this->assertSelectorTextSame('h1', 'Users');
        $this->assertSelectorTextSame('h3', 'Create user');

        $values = [
            'data[email]' => GENERATED_EMAIL,
            'data[passwordPlain][first]' => GENERATED_NAME,
            'data[passwordPlain][second]' => GENERATED_NAME,
        ];
        $this->client->submitForm('Submit', $values);

        $this->assertResponseStatusCodeSame(302);

        $this->client->followRedirect();
        $this->assertPageTitleSame('Users - '.GENERATED_EMAIL);
        $this->assertSelectorTextSame('h1', 'Users');
        $this->assertSelectorTextSame('h2', GENERATED_EMAIL);
        $this->assertSelectorTextSame('h3', 'Summary');
    }

    public function testRead404(): void
    {
        $this->client->request('GET', '/admin/app-users/'.uniqid());

        $this->assertResponseStatusCodeSame(404);
    }

    public function testRead(): void
    {
        $user = $this->appUserManager->getByEmail(GENERATED_EMAIL);

        $this->client->request('GET', '/admin/app-users/'.$user->getId());

        $this->assertResponseStatusCodeSame(200);
        $this->assertPageTitleSame('Users - '.GENERATED_EMAIL);
        $this->assertSelectorTextSame('h1', 'Users');
        $this->assertSelectorTextSame('h2', GENERATED_EMAIL);
        $this->assertSelectorTextSame('h3', 'Summary');
    }

    public function testUpdate404(): void
    {
        $this->client->request('GET', '/admin/app-users/'.uniqid().'/update');

        $this->assertResponseStatusCodeSame(404);
    }

    public function testUpdate(): void
    {
        $user = $this->appUserManager->getByEmail(GENERATED_EMAIL);

        $this->client->request('GET', '/admin/app-users/'.$user->getId().'/update');

        $this->assertResponseStatusCodeSame(200);
        $this->assertPageTitleSame('Users - '.GENERATED_EMAIL.' - Update');
        $this->assertSelectorTextSame('h1', 'Users');
        $this->assertSelectorTextSame('h2', GENERATED_EMAIL);
        $this->assertSelectorTextSame('h3', 'Update');
    }

    public function testDelete404(): void
    {
        $this->client->request('GET', '/admin/app-users/'.uniqid().'/delete');

        $this->assertResponseStatusCodeSame(404);
    }

    public function testDelete(): void
    {
        $user = $this->appUserManager->getByEmail(GENERATED_EMAIL);

        $this->client->request('GET', '/admin/app-users/'.$user->getId().'/delete');

        $this->assertResponseStatusCodeSame(302);
    }
}
