<?php

namespace App\Tests\Controller;

/**
 * @Route("/admin")
 */
class ElasticsearchDeprecationControllerTest extends AbstractAppControllerTest
{
    /**
     * @Route("/deprecations", name="deprecations")
     */
    public function testIndex()
    {
        $this->client->request('GET', '/admin/deprecations');

        $this->assertResponseStatusCodeSame(200);
        $this->assertPageTitleSame('Deprecations');
    }
}
