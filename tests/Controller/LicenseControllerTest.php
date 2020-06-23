<?php

namespace App\Tests\Controller;

/**
 * @Route("/admin")
 */
class LicenseControllerTest extends AbstractAppControllerTest
{
    /**
     * @Route("/license", name="license")
     */
    public function testIndex()
    {
        $this->client->request('GET', '/admin/license');

        $this->assertResponseStatusCodeSame(200);
        $this->assertPageTitleSame('License');
    }
}
