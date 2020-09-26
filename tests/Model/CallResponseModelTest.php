<?php

namespace App\Tests\Model;

use App\Model\CallResponseModel;
use PHPUnit\Framework\TestCase;

class CallResponseModelTest extends TestCase
{
    public function test()
    {
        $callResponse = new CallResponseModel();
        $callResponse->setCode(200);
        $callResponse->setContent(['content']);
        $callResponse->setContentRaw('content-raw');

        $this->assertEquals($callResponse->getCode(), 200);
        $this->assertIsNumeric($callResponse->getCode());

        $this->assertEquals($callResponse->getContent(), ['content']);
        $this->assertIsArray($callResponse->getContent());

        $this->assertEquals($callResponse->getContentRaw(), 'content-raw');
        $this->assertIsString($callResponse->getContentRaw());
    }
}
