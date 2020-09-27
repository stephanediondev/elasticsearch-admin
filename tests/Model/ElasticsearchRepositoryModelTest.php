<?php

namespace App\Tests\Model;

use App\Model\ElasticsearchRepositoryModel;
use PHPUnit\Framework\TestCase;

class ElasticsearchRepositoryModelTest extends TestCase
{
    public function test()
    {
        $repository = new ElasticsearchRepositoryModel();
        $repository->setName('name');
        $repository->setType('type');

        $repository->setSettings(['settings']);

        $repository->setVerify(true);

        $repository->setCompress(true);
        $repository->setChunkSize('chunk-size');
        $repository->setMaxRestoreBytesPerSec('max-restore-bytes-per-sec');
        $repository->setMaxSnapshotBytesPerSec('max-snapshot-bytes-per-sec');
        $repository->setReadonly(true);
        $repository->setLocation('location');
        $repository->setClient('client');
        $repository->setBasePath('base-path');
        $repository->setBucket('bucket');
        $repository->setServerSideEncryption(true);
        $repository->setBufferSize('buffer-size');
        $repository->setCannedAcl('canned-acl');
        $repository->setStorageClass('storage-class');
        $repository->setContainer('container');
        $repository->setLocationMode('location-model');
        $repository->setAwsAccount('aws-account');

        $this->assertEquals($repository->getName(), 'name');
        $this->assertEquals(strval($repository), 'name');
        $this->assertIsString($repository->getName());

        $this->assertEquals($repository->getType(), 'type');
        $this->assertIsString($repository->getType());

        $this->assertEquals($repository->getSettings(), ['settings']);
        $this->assertIsArray($repository->getSettings());

        $this->assertEquals($repository->getVerify(), true);
        $this->assertIsBool($repository->getVerify());

        $this->assertEquals($repository->getCompress(), true);
        $this->assertIsBool($repository->getCompress());

        $this->assertEquals($repository->getChunkSize(), 'chunk-size');
        $this->assertIsString($repository->getChunkSize());

        $this->assertEquals($repository->getMaxRestoreBytesPerSec(), 'max-restore-bytes-per-sec');
        $this->assertIsString($repository->getMaxRestoreBytesPerSec());

        $this->assertEquals($repository->getMaxSnapshotBytesPerSec(), 'max-snapshot-bytes-per-sec');
        $this->assertIsString($repository->getMaxSnapshotBytesPerSec());

        $this->assertEquals($repository->getReadonly(), true);
        $this->assertIsBool($repository->getReadonly());

        $this->assertEquals($repository->getLocation(), 'location');
        $this->assertIsString($repository->getLocation());

        $this->assertEquals($repository->getClient(), 'client');
        $this->assertIsString($repository->getClient());

        $this->assertEquals($repository->getBasePath(), 'base-path');
        $this->assertIsString($repository->getBasePath());

        $this->assertEquals($repository->getBucket(), 'bucket');
        $this->assertIsString($repository->getBucket());

        $this->assertEquals($repository->getServerSideEncryption(), true);
        $this->assertIsBool($repository->getServerSideEncryption());

        $this->assertEquals($repository->getBufferSize(), 'buffer-size');
        $this->assertIsString($repository->getBufferSize());

        $this->assertEquals($repository->getCannedAcl(), 'canned-acl');
        $this->assertIsString($repository->getCannedAcl());

        $this->assertEquals($repository->getStorageClass(), 'storage-class');
        $this->assertIsString($repository->getStorageClass());

        $this->assertEquals($repository->getContainer(), 'container');
        $this->assertIsString($repository->getContainer());

        $this->assertEquals($repository->getLocationMode(), 'location-model');
        $this->assertIsString($repository->getLocationMode());

        $this->assertEquals($repository->getAwsAccount(), 'aws-account');
        $this->assertIsString($repository->getAwsAccount());
    }
}
