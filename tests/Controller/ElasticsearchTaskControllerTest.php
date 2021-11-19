<?php

namespace App\Tests\Controller;

/**
 * @Route("/admin")
 */
class ElasticsearchTaskControllerTest extends AbstractAppControllerTest
{
    /**
     * @Route("/tasks", name="tasks")
     */
    public function testIndex(): void
    {
        $this->client->request('GET', '/admin/tasks');

        if (false == $this->callManager->hasFeature('tasks')) {
            $this->assertResponseStatusCodeSame(403);
        } else {
            $this->assertResponseStatusCodeSame(200);
            $this->assertPageTitleSame('Tasks');
            $this->assertSelectorTextSame('h1', 'Tasks');
            $this->assertSelectorTextContains('h3', 'List');
        }
    }
}
