<?php

namespace App\Tests\Controller;

/**
 * @Route("/admin")
 */
class ElasticsearchConsoleControllerTest extends AbstractAppControllerTest
{
    /**
     * @Route("/console", name="console")
     */
    public function testIndex(): void
    {
        $this->client->request('GET', '/admin/console');

        $this->assertResponseStatusCodeSame(200);
        $this->assertPageTitleSame('Console');
        $this->assertSelectorTextSame('h1', 'Console');
    }
}
