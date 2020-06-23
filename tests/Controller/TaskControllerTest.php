<?php

namespace App\Tests\Controller;

/**
 * @Route("/admin")
 */
class TaskControllerTest extends AbstractAppControllerTest
{
    /**
     * @Route("/tasks", name="tasks")
     */
    public function testIndex()
    {
        $this->client->request('GET', '/admin/tasks');

        $this->assertResponseStatusCodeSame(200);
        $this->assertPageTitleSame('Tasks');
    }
}
