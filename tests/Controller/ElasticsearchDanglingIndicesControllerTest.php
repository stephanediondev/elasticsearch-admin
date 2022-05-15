<?php

namespace App\Tests\Controller;

use App\Tests\Controller\AbstractAppControllerTest;

#[Route('/admin')]
class ElasticsearchDanglingIndicesControllerTest extends AbstractAppControllerTest
{
    #[Route('/dangling-indices', name: 'dangling_indices')]
    public function testIndex(): void
    {
        $this->client->request('GET', '/admin/dangling-indices');

        if (false == $this->callManager->hasFeature('dangling_indices')) {
            $this->assertResponseStatusCodeSame(403);
        } else {
            $this->assertResponseStatusCodeSame(200);
            $this->assertPageTitleSame('Dangling indices');
            $this->assertSelectorTextSame('h1', 'Dangling indices');
            $this->assertSelectorTextContains('h3', 'List');
        }
    }
}
