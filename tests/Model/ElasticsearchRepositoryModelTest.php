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

        $this->assertEquals($repository->getType(), 'type');

        $this->assertEquals($repository->getSettings(), ['settings']);
        $this->assertIsArray($repository->getSettings());

        $this->assertEquals($repository->getVerify(), true);
        $this->assertIsBool($repository->getVerify());

        $this->assertEquals($repository->getCompress(), true);
        $this->assertIsBool($repository->getCompress());
        $this->assertEquals($repository->getChunkSize(), 'chunk-size');
        $this->assertEquals($repository->getMaxRestoreBytesPerSec(), 'max-restore-bytes-per-sec');
        $this->assertEquals($repository->getMaxSnapshotBytesPerSec(), 'max-snapshot-bytes-per-sec');
        $this->assertEquals($repository->getReadonly(), true);
        $this->assertIsBool($repository->getReadonly());
        $this->assertEquals($repository->getLocation(), 'location');
        $this->assertEquals($repository->getClient(), 'client');
        $this->assertEquals($repository->getBasePath(), 'base-path');
        $this->assertEquals($repository->getBucket(), 'bucket');
        $this->assertEquals($repository->getServerSideEncryption(), true);
        $this->assertIsBool($repository->getServerSideEncryption());
        $this->assertEquals($repository->getBufferSize(), 'buffer-size');
        $this->assertEquals($repository->getCannedAcl(), 'canned-acl');
        $this->assertEquals($repository->getStorageClass(), 'storage-class');
        $this->assertEquals($repository->getContainer(), 'container');
        $this->assertEquals($repository->getLocationMode(), 'location-model');
        $this->assertEquals($repository->getAwsAccount(), 'aws-account');
    }
}
