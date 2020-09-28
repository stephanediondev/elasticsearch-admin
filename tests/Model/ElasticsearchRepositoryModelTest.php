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

        $this->assertEquals($repository->getJson(), [
            'type' => $repository->getType(),
            'settings' => [
                'compress' => $repository->getCompress(),
                'chunk_size' => $repository->getChunkSize(),
                'max_restore_bytes_per_sec' => $repository->getMaxRestoreBytesPerSec(),
                'max_snapshot_bytes_per_sec' => $repository->getMaxSnapshotBytesPerSec(),
                'readonly' => $repository->getReadonly(),
            ],
        ]);
        $this->assertIsArray($repository->getJson());

        $repository->setType('fs');
        $this->assertEquals($repository->getJson(), [
            'type' => $repository->getType(),
            'settings' => [
                'compress' => $repository->getCompress(),
                'chunk_size' => $repository->getChunkSize(),
                'max_restore_bytes_per_sec' => $repository->getMaxRestoreBytesPerSec(),
                'max_snapshot_bytes_per_sec' => $repository->getMaxSnapshotBytesPerSec(),
                'readonly' => $repository->getReadonly(),
                'location' => $repository->getLocation(),
            ],
        ]);
        $this->assertIsArray($repository->getJson());

        $repository->setType('s3');
        $this->assertEquals($repository->getJson(), [
            'type' => $repository->getType(),
            'settings' => [
                'compress' => $repository->getCompress(),
                'chunk_size' => $repository->getChunkSize(),
                'max_restore_bytes_per_sec' => $repository->getMaxRestoreBytesPerSec(),
                'max_snapshot_bytes_per_sec' => $repository->getMaxSnapshotBytesPerSec(),
                'readonly' => $repository->getReadonly(),
                'bucket' => $repository->getBucket(),
                'client' => $repository->getClient(),
                'base_path' => $repository->getBasePath(),
                'server_side_encryption' => $repository->getServerSideEncryption(),
                'buffer_size' => $repository->getBufferSize(),
                'canned_acl' => $repository->getCannedAcl(),
                'storage_class' => $repository->getStorageClass(),
            ],
        ]);
        $this->assertIsArray($repository->getJson());

        $repository->setType('gcs');
        $this->assertEquals($repository->getJson(), [
            'type' => $repository->getType(),
            'settings' => [
                'compress' => $repository->getCompress(),
                'chunk_size' => $repository->getChunkSize(),
                'max_restore_bytes_per_sec' => $repository->getMaxRestoreBytesPerSec(),
                'max_snapshot_bytes_per_sec' => $repository->getMaxSnapshotBytesPerSec(),
                'readonly' => $repository->getReadonly(),
                'bucket' => $repository->getBucket(),
                'client' => $repository->getClient(),
                'base_path' => $repository->getBasePath(),
            ],
        ]);
        $this->assertIsArray($repository->getJson());

        $repository->setType('azure');
        $this->assertEquals($repository->getJson(), [
            'type' => $repository->getType(),
            'settings' => [
                'compress' => $repository->getCompress(),
                'chunk_size' => $repository->getChunkSize(),
                'max_restore_bytes_per_sec' => $repository->getMaxRestoreBytesPerSec(),
                'max_snapshot_bytes_per_sec' => $repository->getMaxSnapshotBytesPerSec(),
                'readonly' => $repository->getReadonly(),
                'container' => $repository->getContainer(),
                'client' => $repository->getClient(),
                'base_path' => $repository->getBasePath(),
                'location_mode' => $repository->getLocationMode(),
            ],
        ]);
        $this->assertIsArray($repository->getJson());
    }
}
