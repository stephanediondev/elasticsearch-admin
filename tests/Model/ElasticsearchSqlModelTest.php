<?php

namespace App\Tests\Model;

use App\Model\ElasticsearchSqlModel;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class ElasticsearchSqlModelTest extends WebTestCase
{
    public function test()
    {
        $sql = new ElasticsearchSqlModel();
        $sql->setQuery('query');
        $sql->setFilter('filter');
        $sql->setFetchSize(1);

        $this->assertEquals($sql->getQuery(), 'query');
        $this->assertEquals($sql->getFilter(), 'filter');

        $this->assertEquals($sql->getFetchSize(), 1);
        $this->assertIsInt($sql->getFetchSize());
    }
}
