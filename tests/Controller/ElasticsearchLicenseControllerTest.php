<?php

namespace App\Tests\Controller;

/**
 * @Route("/admin")
 */
class ElasticsearchLicenseControllerTest extends AbstractAppControllerTest
{
    /**
     * @Route("/license", name="license")
     */
    public function testIndex(): void
    {
        $this->client->request('GET', '/admin/license');

        if (false == $this->callManager->hasFeature('license')) {
            $this->assertResponseStatusCodeSame(403);
        } else {
            $this->assertResponseStatusCodeSame(200);
            $this->assertPageTitleSame('License');
            $this->assertSelectorTextSame('h1', 'License');
            $this->assertSelectorTextSame('h3', 'Summary');
        }
    }
}
