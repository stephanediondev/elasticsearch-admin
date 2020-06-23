<?php

namespace App\Tests\Controller;

/**
 * @Route("/admin")
 */
class ConsoleControllerTest extends AbstractAppControllerTest
{
    /**
     * @Route("/console", name="console")
     */
    public function testIndex()
    {
        $this->client->request('GET', '/admin/console');

        $this->assertResponseStatusCodeSame(200);
        $this->assertPageTitleSame('Console');
    }
}
