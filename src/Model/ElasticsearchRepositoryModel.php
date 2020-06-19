<?php

namespace App\Model;

use App\Model\AbstractAppModel;

class ElasticsearchRepositoryModel extends AbstractAppModel
{
    const TYPE_FS = 'fs';
    const TYPE_S3 = 's3';
    const TYPE_GCS = 'gcs';

    private $type;

    private $name;

    private $compress;

    private $chunkSize;

    private $maxRestoreBytesPerSec;

    private $maxSnapshotBytesPerSec;

    private $readonly;

    private $verify;

    private $location;

    private $bucket;

    private $client;

    private $basePath;

    private $serverSideEncryption;

    private $bufferSize;

    private $cannedAcl;

    private $storageClass;

    public function __construct()
    {
        $this->compress = true;
        $this->chunkSize = null;
        $this->maxRestoreBytesPerSec = '40mb';
        $this->maxSnapshotBytesPerSec = '40mb';
        $this->readonly = false;
        $this->verify = true;

        $this->client = 'default';
        $this->cannedAcl = 'private';
        $this->storageClass = 'standard';
    }

    public function getType(): ?string
    {
        return $this->type;
    }

    public function setType(?string $type): self
    {
        $this->type = $type;

        return $this;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(?string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function getCompress(): ?bool
    {
        return $this->compress;
    }

    public function setCompress(?bool $compress): self
    {
        $this->compress = $compress;

        return $this;
    }

    public function getChunkSize(): ?string
    {
        return $this->chunkSize;
    }

    public function setChunkSize(?string $chunkSize): self
    {
        $this->chunkSize = $chunkSize;

        return $this;
    }

    public function getMaxRestoreBytesPerSec(): ?string
    {
        return $this->maxRestoreBytesPerSec;
    }

    public function setMaxRestoreBytesPerSec(?string $maxRestoreBytesPerSec): self
    {
        $this->maxRestoreBytesPerSec = $maxRestoreBytesPerSec;

        return $this;
    }

    public function getMaxSnapshotBytesPerSec(): ?string
    {
        return $this->maxSnapshotBytesPerSec;
    }

    public function setMaxSnapshotBytesPerSec(?string $maxSnapshotBytesPerSec): self
    {
        $this->maxSnapshotBytesPerSec = $maxSnapshotBytesPerSec;

        return $this;
    }

    public function getReadonly(): ?bool
    {
        return $this->readonly;
    }

    public function setReadonly(?bool $readonly): self
    {
        $this->readonly = $readonly;

        return $this;
    }

    public function getVerify(): ?bool
    {
        return $this->verify;
    }

    public function setVerify(?bool $verify): self
    {
        $this->verify = $verify;

        return $this;
    }

    public static function allowedTypes(): ?array
    {
        return [
            self::TYPE_FS => self::TYPE_FS,
            self::TYPE_S3 => self::TYPE_S3,
            self::TYPE_GCS => self::TYPE_GCS,
        ];
    }

    public function convert(?array $repository): self
    {
        $this->setName($repository['id']);
        $this->setType($repository['type']);

        if (true == isset($repository['settings']) && 0 < count($repository['settings'])) {
            if (true == isset($repository['settings']['compress'])) {
                $this->setCompress($this->convertBoolean($repository['settings']['compress']));
            }
            if (true == isset($repository['settings']['chunk_size'])) {
                $this->setChunkSize($repository['settings']['chunk_size']);
            }
            if (true == isset($repository['settings']['max_restore_bytes_per_sec'])) {
                $this->setMaxRestoreBytesPerSec($repository['settings']['max_restore_bytes_per_sec']);
            }
            if (true == isset($repository['settings']['max_snapshot_bytes_per_sec'])) {
                $this->setMaxSnapshotBytesPerSec($repository['settings']['max_snapshot_bytes_per_sec']);
            }
            if (true == isset($repository['settings']['readonly'])) {
                $this->setReadonly($this->convertBoolean($repository['settings']['readonly']));
            }

            // TYPE_FS
            if (true == isset($repository['settings']['location'])) {
                $this->setLocation($repository['settings']['location']);
            }

            // TYPE_S3 or TYPE_GCS
            if (true == isset($repository['settings']['bucket'])) {
                $this->setBucket($repository['settings']['bucket']);
            }
            if (true == isset($repository['settings']['client'])) {
                $this->setClient($repository['settings']['client']);
            }
            if (true == isset($repository['settings']['base_path'])) {
                $this->setBasePath($repository['settings']['base_path']);
            }

            // TYPE_S3
            if (true == isset($repository['settings']['server_side_encryption'])) {
                $this->setServerSideEncryption($this->convertBoolean($repository['settings']['server_side_encryption']));
            }
            if (true == isset($repository['settings']['buffer_size'])) {
                $this->setBufferSize($repository['settings']['buffer_size']);
            }
            if (true == isset($repository['settings']['canned_acl'])) {
                $this->setCannedAcl($repository['settings']['canned_acl']);
            }
            if (true == isset($repository['settings']['storage_class'])) {
                $this->setStorageClass($repository['settings']['storage_class']);
            }
        }
        return $this;
    }


    // TYPE_FS
    public function getLocation(): ?string
    {
        return $this->location;
    }

    public function setLocation(?string $location): self
    {
        $this->location = $location;

        return $this;
    }

    // TYPE_S3 or TYPE_GCS
    public function getBucket(): ?string
    {
        return $this->bucket;
    }

    public function setBucket(?string $bucket): self
    {
        $this->bucket = $bucket;

        return $this;
    }

    public function getClient(): ?string
    {
        return $this->client;
    }

    public function setClient(?string $client): self
    {
        $this->client = $client;

        return $this;
    }

    public function getBasePath(): ?string
    {
        return $this->basePath;
    }

    public function setBasePath(?string $basePath): self
    {
        $this->basePath = $basePath;

        return $this;
    }

    // TYPE_S3
    public function getServerSideEncryption(): ?bool
    {
        return $this->serverSideEncryption;
    }

    public function setServerSideEncryption(?bool $serverSideEncryption): self
    {
        $this->serverSideEncryption = $serverSideEncryption;

        return $this;
    }

    public function getBufferSize(): ?string
    {
        return $this->bufferSize;
    }

    public function setBufferSize(?string $bufferSize): self
    {
        $this->bufferSize = $bufferSize;

        return $this;
    }

    public function getCannedAcl(): ?string
    {
        return $this->cannedAcl;
    }

    public function setCannedAcl(?string $cannedAcl): self
    {
        $this->cannedAcl = $cannedAcl;

        return $this;
    }

    public function getStorageClass(): ?string
    {
        return $this->storageClass;
    }

    public function setStorageClass(?string $storageClass): self
    {
        $this->storageClass = $storageClass;

        return $this;
    }

    public static function cannedAcls(): ?array
    {
        return [
            'private' => 'private',
            'public-read' => 'public-read',
            'public-read-write' => 'public-read-write',
            'authenticated-read' => 'authenticated-read',
            'log-delivery-write' => 'log-delivery-write',
            'bucket-owner-read' => 'bucket-owner-read',
            'bucket-owner-full-control' => 'bucket-owner-full-control',
        ];
    }

    public static function storageClasses(): ?array
    {
        return [
            'standard' => 'standard',
            'reduced_redundancy' => 'reduced_redundancy',
            'standard_ia' => 'standard_ia',
            'onezone_ia' => 'onezone_ia',
            'intelligent_tiering' => 'intelligent_tiering',
        ];
    }
}
