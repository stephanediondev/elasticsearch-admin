<?php

namespace App\Tests\Model;

use App\Model\ElasticsearchSnapshotRestoreModel;
use PHPUnit\Framework\TestCase;

class ElasticsearchSnapshotRestoreModelTest extends TestCase
{
    public function test()
    {
        $snapshotRestoreModel = new ElasticsearchSnapshotRestoreModel();
        $snapshotRestoreModel->setRenamePattern('rename-pattern');
        $snapshotRestoreModel->setRenameReplacement('rename-replacement');

        $this->assertEquals($snapshotRestoreModel->getRenamePattern(), 'rename-pattern');
        $this->assertIsString($snapshotRestoreModel->getRenamePattern());

        $this->assertEquals($snapshotRestoreModel->getRenameReplacement(), 'rename-replacement');
        $this->assertIsString($snapshotRestoreModel->getRenameReplacement());
    }
}
