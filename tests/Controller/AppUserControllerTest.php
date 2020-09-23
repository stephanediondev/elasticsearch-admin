<?php

namespace App\Tests\Controller;

/**
 * @Route("/admin")
 */
class AppUserControllerTest extends AbstractAppControllerTest
{
    /**
     * @Route("/app-users", name="app_users")
     */
    public function testIndex()
    {
        $this->client->request('GET', '/admin/app-users');

        $this->assertResponseStatusCodeSame(200);
        $this->assertPageTitleSame('Users');
        $this->assertSelectorTextSame('h1', 'Users');
        $this->assertSelectorTextContains('h3', 'List');
    }

    /**
     * @Route("/app-users/create", name="app_users_create")
     */
    public function testCreate()
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

    /**
     * @Route("/app-users/{user}", name="app_users_read")
     */
    public function testRead404()
    {
        $this->client->request('GET', '/admin/app-users/'.uniqid());

        $this->assertResponseStatusCodeSame(404);
    }

    public function testRead()
    {
        $user = $this->appUserManager->getByEmail(GENERATED_EMAIL);

        $this->client->request('GET', '/admin/app-users/'.$user->getId());

        $this->assertResponseStatusCodeSame(200);
        $this->assertPageTitleSame('Users - '.GENERATED_EMAIL);
        $this->assertSelectorTextSame('h1', 'Users');
        $this->assertSelectorTextSame('h2', GENERATED_EMAIL);
        $this->assertSelectorTextSame('h3', 'Summary');
    }

    /**
     * @Route("/app-users/{user}/update", name="app_users_update")
     */
    public function testUpdate404()
    {
        $this->client->request('GET', '/admin/app-users/'.uniqid().'/update');

        $this->assertResponseStatusCodeSame(404);
    }

    public function testUpdate()
    {
        $user = $this->appUserManager->getByEmail(GENERATED_EMAIL);

        $this->client->request('GET', '/admin/app-users/'.$user->getId().'/update');

        $this->assertResponseStatusCodeSame(200);
        $this->assertPageTitleSame('Users - '.GENERATED_EMAIL.' - Update');
        $this->assertSelectorTextSame('h1', 'Users');
        $this->assertSelectorTextSame('h2', GENERATED_EMAIL);
        $this->assertSelectorTextSame('h3', 'Update');
    }

    /**
     * @Route("/app-users/{user}/delete", name="app_users_delete")
     */
    public function testDelete404()
    {
        $this->client->request('GET', '/admin/app-users/'.uniqid().'/delete');

        $this->assertResponseStatusCodeSame(404);
    }

    public function testDelete()
    {
        $user = $this->appUserManager->getByEmail(GENERATED_EMAIL);

        $this->client->request('GET', '/admin/app-users/'.$user->getId().'/delete');

        $this->assertResponseStatusCodeSame(302);
    }
}
