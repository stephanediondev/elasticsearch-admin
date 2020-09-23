<?php

namespace App\Tests\Controller;

use App\Model\CallRequestModel;

/**
 * @Route("/admin")
 */
class AppIndexDatabaseImportControllerTest extends AbstractAppControllerTest
{
    /**
     * @Route("/indices/{index}/database-import", name="index_database_import")
     */
    public function testIndex()
    {
        $this->client->request('GET', '/admin/indices/elasticsearch-admin-test/database-import');

        $this->assertResponseStatusCodeSame(200);
        $this->assertPageTitleSame('Indices - elasticsearch-admin-test - Import from database');
        $this->assertSelectorTextSame('h1', 'Indices');
        $this->assertSelectorTextSame('h3', 'Import from database');
    }
}
