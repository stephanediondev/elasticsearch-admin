<?php

namespace App\Tests\Model;

use App\Model\ElasticsearchEnrichPolicyModel;
use PHPUnit\Framework\TestCase;

class ElasticsearchEnrichPolicyModelTest extends TestCase
{
    public function test(): void
    {
        $enrichPolicyModel = new ElasticsearchEnrichPolicyModel();
        $enrichPolicyModel->setName('name');
        $enrichPolicyModel->setType('type');
        $enrichPolicyModel->setIndices(['indices']);
        $enrichPolicyModel->setMatchField('match-field');
        $enrichPolicyModel->setEnrichFields(['enrich-fields']);
        $enrichPolicyModel->setQuery('query');

        $this->assertEquals($enrichPolicyModel->getName(), 'name');
        $this->assertEquals(strval($enrichPolicyModel), 'name');
        $this->assertIsString($enrichPolicyModel->getName());

        $this->assertEquals($enrichPolicyModel->getType(), 'type');
        $this->assertIsString($enrichPolicyModel->getType());

        $this->assertEquals($enrichPolicyModel->getIndices(), ['indices']);
        $this->assertIsArray($enrichPolicyModel->getIndices());

        $this->assertEquals($enrichPolicyModel->getMatchField(), 'match-field');
        $this->assertIsString($enrichPolicyModel->getMatchField());

        $this->assertEquals($enrichPolicyModel->getEnrichFields(), ['enrich-fields']);
        $this->assertIsArray($enrichPolicyModel->getEnrichFields());

        $this->assertEquals($enrichPolicyModel->getQuery(), 'query');
        $this->assertIsString($enrichPolicyModel->getQuery());

        $this->assertEquals($enrichPolicyModel->isSystem(), false);
        $this->assertIsBool($enrichPolicyModel->isSystem());

        $enrichPolicyModel->setName('.name');
        $this->assertEquals($enrichPolicyModel->isSystem(), true);
        $this->assertIsBool($enrichPolicyModel->isSystem());

        $this->assertEquals($enrichPolicyModel->getJson(), [
            $enrichPolicyModel->getType() => [
                'indices' => $enrichPolicyModel->getIndices(),
                'match_field' => $enrichPolicyModel->getMatchField(),
                'enrich_fields' => $enrichPolicyModel->getEnrichFields(),
                'query' => $enrichPolicyModel->getQuery(),
            ],
        ]);
        $this->assertIsArray($enrichPolicyModel->getJson());
    }
}
