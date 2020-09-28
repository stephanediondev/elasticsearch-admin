<?php

namespace App\Tests\Model;

use App\Model\ElasticsearchSqlModel;
use PHPUnit\Framework\TestCase;

class ElasticsearchSqlModelTest extends TestCase
{
    public function test()
    {
        $sql = new ElasticsearchSqlModel();
        $sql->setQuery('query');
        $sql->setFilter('filter');
        $sql->setFetchSize(1);

        $this->assertEquals($sql->getQuery(), 'query');
        $this->assertIsString($sql->getQuery());

        $this->assertEquals($sql->getFilter(), 'filter');
        $this->assertIsString($sql->getFilter());

        $this->assertEquals($sql->getFetchSize(), 1);
        $this->assertIsInt($sql->getFetchSize());

        $this->assertEquals($sql->getJson(), [
            'query' => $sql->getQuery(),
            'fetch_size' => $sql->getFetchSize(),
            'filter' => json_decode($sql->getFilter(), true),
        ]);
        $this->assertIsArray($sql->getJson());
    }
}
