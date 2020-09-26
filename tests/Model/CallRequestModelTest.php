<?php

namespace App\Tests\Model;

use App\Model\CallRequestModel;
use PHPUnit\Framework\TestCase;

class CallRequestModelTest extends TestCase
{
    public function test()
    {
        $callRequest = new CallRequestModel();
        $callRequest->setMethod('method');
        $callRequest->setPath('path');
        $callRequest->setOptions([]);
        $callRequest->setQuery(['query']);
        $callRequest->setJson(['json']);
        $callRequest->setBody('body');

        $this->assertEquals($callRequest->getMethod(), 'method');
        $this->assertIsString($callRequest->getMethod());

        $this->assertEquals($callRequest->getPath(), '/path');
        $this->assertIsString($callRequest->getPath());

        $this->assertEquals($callRequest->getOptions(), ['query' => ['query'], 'json' => ['json'], 'body' => 'body']);
        $this->assertIsArray($callRequest->getOptions());

        $this->assertEquals($callRequest->getQuery(), ['query']);
        $this->assertIsArray($callRequest->getQuery());

        $this->assertEquals($callRequest->getJson(), ['json']);
        $this->assertIsArray($callRequest->getJson());

        $this->assertEquals($callRequest->getBody(), 'body');
        $this->assertIsString($callRequest->getBody());
    }
}
