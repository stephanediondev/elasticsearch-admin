<?php

namespace App\Tests\Controller;

/**
 * @Route("/admin")
 */
class ElasticsearchSqlControllerTest extends AbstractAppControllerTest
{
    /**
     * @Route("/sql", name="sql")
     */
    public function testIndex()
    {
        $this->client->request('GET', '/admin/sql');

        if (false == $this->callManager->hasFeature('sql')) {
            $this->assertResponseStatusCodeSame(403);
        } else {
            $this->assertResponseStatusCodeSame(200);
            $this->assertPageTitleSame('SQL access');
            $this->assertSelectorTextSame('h1', 'SQL access');
        }
    }
}
