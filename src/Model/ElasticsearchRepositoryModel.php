<?php
declare(strict_types=1);

namespace App\Model;

use App\Model\AbstractAppModel;

class ElasticsearchRepositoryModel extends AbstractAppModel
{
    const TYPE_FS = 'fs';
    const TYPE_S3 = 's3';
    const TYPE_GCS = 'gcs';
    const TYPE_AZURE = 'azure';

    private ?string $type = null;

    private ?string $name = null;

    private ?bool $compress = null;

    private ?string $chunkSize = null;

    private ?string $maxRestoreBytesPerSec = null;

    private ?string $maxSnapshotBytesPerSec = null;

    private ?bool $readonly = null;

    private ?bool $verify = null;

    private ?string $location = null;

    private ?string $bucket = null;

    private ?string $client = null;

    private ?string $basePath = null;

    private ?bool $serverSideEncryption = null;

    private ?string $bufferSize = null;

    private ?string $cannedAcl = null;

    private ?string $storageClass = null;

    private ?string $awsAccount = null;

    private ?array $settings = null;

    private ?string $container = null;

    private ?string $locationMode = null;

    public function __construct()
    {
        $this->compress = true;
        $this->chunkSize = null;
        $this->readonly = false;
        $this->verify = true;

        $this->client = 'default';
        $this->cannedAcl = 'private';
        $this->storageClass = 'standard';
        $this->locationMode = 'primary_only';
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

    public function getSettings(): ?array
    {
        return $this->settings;
    }

    public function setSettings(?array $settings): self
    {
        $this->settings = $settings;

        return $this;
    }

    public function convert(?array $repository): self
    {
        $this->setName($repository['name']);
        $this->setType($repository['type']);

        if (true === isset($repository['settings']) && 0 < count($repository['settings'])) {
            $this->setSettings($repository['settings']);

            if (true === isset($repository['settings']['compress'])) {
                $this->setCompress($this->convertBoolean($repository['settings']['compress']));
            }
            if (true === isset($repository['settings']['chunk_size'])) {
                $this->setChunkSize($repository['settings']['chunk_size']);
            }
            if (true === isset($repository['settings']['max_restore_bytes_per_sec'])) {
                $this->setMaxRestoreBytesPerSec($repository['settings']['max_restore_bytes_per_sec']);
            }
            if (true === isset($repository['settings']['max_snapshot_bytes_per_sec'])) {
                $this->setMaxSnapshotBytesPerSec($repository['settings']['max_snapshot_bytes_per_sec']);
            }
            if (true === isset($repository['settings']['readonly'])) {
                $this->setReadonly($this->convertBoolean($repository['settings']['readonly']));
            }

            // TYPE_FS
            if (true === isset($repository['settings']['location'])) {
                $this->setLocation($repository['settings']['location']);
            }

            // TYPE_S3, TYPE_GCS or TYPE_AZURE
            if (true === isset($repository['settings']['client'])) {
                $this->setClient($repository['settings']['client']);
            }
            if (true === isset($repository['settings']['base_path'])) {
                $this->setBasePath($repository['settings']['base_path']);
            }

            // TYPE_S3 or TYPE_GCS
            if (true === isset($repository['settings']['bucket'])) {
                $this->setBucket($repository['settings']['bucket']);
            }

            // TYPE_S3
            if (true === isset($repository['settings']['server_side_encryption'])) {
                $this->setServerSideEncryption($this->convertBoolean($repository['settings']['server_side_encryption']));
            }
            if (true === isset($repository['settings']['buffer_size'])) {
                $this->setBufferSize($repository['settings']['buffer_size']);
            }
            if (true === isset($repository['settings']['canned_acl'])) {
                $this->setCannedAcl($repository['settings']['canned_acl']);
            }
            if (true === isset($repository['settings']['storage_class'])) {
                $this->setStorageClass($repository['settings']['storage_class']);
            }

            // TYPE_AZURE
            if (true === isset($repository['settings']['container'])) {
                $this->setContainer($repository['settings']['container']);
            }
            if (true === isset($repository['settings']['location_mode'])) {
                $this->setLocationMode($repository['settings']['location_mode']);
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

    public function getAwsAccount(): ?string
    {
        return $this->awsAccount;
    }

    public function setAwsAccount(?string $awsAccount): self
    {
        $this->awsAccount = $awsAccount;

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

    // TYPE_AZURE
    public function getContainer(): ?string
    {
        return $this->container;
    }

    public function setContainer(?string $container): self
    {
        $this->container = $container;

        return $this;
    }

    public function getLocationMode(): ?string
    {
        return $this->locationMode;
    }

    public function setLocationMode(?string $locationMode): self
    {
        $this->locationMode = $locationMode;

        return $this;
    }

    public static function locationModes(): ?array
    {
        return [
            'primary_only' => 'primary_only',
            'secondary_only' => 'secondary_only',
        ];
    }

    public function getJson(): array
    {
        $json = [
            'type' => $this->getType(),
            'settings' => [
                'compress' => $this->getCompress(),
                'chunk_size' => $this->getChunkSize(),
                'max_restore_bytes_per_sec' => $this->getMaxRestoreBytesPerSec(),
                'max_snapshot_bytes_per_sec' => $this->getMaxSnapshotBytesPerSec(),
                'readonly' => $this->getReadonly(),
            ],
        ];

        if (self::TYPE_FS == $this->getType()) {
            $json['settings']['location'] = $this->getLocation();
        }

        if (self::TYPE_S3 == $this->getType()) {
            $json['settings']['bucket'] = $this->getBucket();
            $json['settings']['client'] = $this->getClient();
            $json['settings']['base_path'] = $this->getBasePath();
            $json['settings']['server_side_encryption'] = $this->getServerSideEncryption();
            $json['settings']['buffer_size'] = $this->getBufferSize();
            $json['settings']['canned_acl'] = $this->getCannedAcl();
            $json['settings']['storage_class'] = $this->getStorageClass();
        }

        if (self::TYPE_GCS == $this->getType()) {
            $json['settings']['bucket'] = $this->getBucket();
            $json['settings']['client'] = $this->getClient();
            $json['settings']['base_path'] = $this->getBasePath();
        }

        if (self::TYPE_AZURE == $this->getType()) {
            $json['settings']['container'] = $this->getContainer();
            $json['settings']['client'] = $this->getClient();
            $json['settings']['base_path'] = $this->getBasePath();
            $json['settings']['location_mode'] = $this->getLocationMode();
        }

        return $json;
    }

    public function __toString(): string
    {
        return $this->name;
    }

    public static function getTypes(): ?array
    {
        return [
            self::TYPE_FS,
            self::TYPE_S3,
            self::TYPE_GCS,
            self::TYPE_AZURE,
        ];
    }
}
