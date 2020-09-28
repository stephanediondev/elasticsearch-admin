<?php

namespace App\Tests\Model;

use App\Model\ElasticsearchDataStreamModel;
use PHPUnit\Framework\TestCase;

class ElasticsearchDataStreamModelTest extends TestCase
{
    public function test()
    {
        $datastream = new ElasticsearchDataStreamModel();
        $datastream->setName('name');
        $datastream->setTimestampFieldName('timestamp-field-name');

        $datastream->setIndices(['indices']);

        $datastream->setGeneration(1);

        $datastream->setStatus('status');
        $datastream->setTemplate('template');
        $datastream->setIlmPolicy('ilm-policy');

        $this->assertEquals($datastream->getName(), 'name');
        $this->assertEquals(strval($datastream), 'name');
        $this->assertIsString($datastream->getName());

        $this->assertEquals($datastream->getTimestampFieldName(), 'timestamp-field-name');
        $this->assertIsString($datastream->getTimestampFieldName());

        $this->assertEquals($datastream->getIndices(), ['indices']);
        $this->assertIsArray($datastream->getIndices());

        $this->assertEquals($datastream->getGeneration(), 1);
        $this->assertIsNumeric($datastream->getGeneration());

        $this->assertEquals($datastream->getStatus(), 'status');
        $this->assertIsString($datastream->getStatus());

        $this->assertEquals($datastream->getTemplate(), 'template');
        $this->assertIsString($datastream->getTemplate());

        $this->assertEquals($datastream->getIlmPolicy(), 'ilm-policy');
        $this->assertIsString($datastream->getIlmPolicy());
    }
}
