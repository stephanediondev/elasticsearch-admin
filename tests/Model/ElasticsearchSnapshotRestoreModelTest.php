<?php

namespace App\Tests\Model;

use App\Model\ElasticsearchSnapshotRestoreModel;
use PHPUnit\Framework\TestCase;

class ElasticsearchSnapshotRestoreModelTest extends TestCase
{
    public function test()
    {
        $snapshotRestoreModel = new ElasticsearchSnapshotRestoreModel();
        $snapshotRestoreModel->setIndices(['indices']);
        $snapshotRestoreModel->setRenamePattern('rename-pattern');
        $snapshotRestoreModel->setRenameReplacement('rename-replacement');
        $snapshotRestoreModel->setIgnoreUnavailable(true);
        $snapshotRestoreModel->setPartial(true);
        $snapshotRestoreModel->setIncludeGlobalState(true);

        $this->assertEquals($snapshotRestoreModel->getIndices(), ['indices']);
        $this->assertIsArray($snapshotRestoreModel->getIndices());

        $this->assertEquals($snapshotRestoreModel->getIgnoreUnavailable(), true);
        $this->assertIsBool($snapshotRestoreModel->getIgnoreUnavailable());

        $this->assertEquals($snapshotRestoreModel->getPartial(), true);
        $this->assertIsBool($snapshotRestoreModel->getPartial());

        $this->assertEquals($snapshotRestoreModel->getIncludeGlobalState(), true);
        $this->assertIsBool($snapshotRestoreModel->getIncludeGlobalState());

        $this->assertEquals($snapshotRestoreModel->getRenamePattern(), 'rename-pattern');
        $this->assertIsString($snapshotRestoreModel->getRenamePattern());

        $this->assertEquals($snapshotRestoreModel->getRenameReplacement(), 'rename-replacement');
        $this->assertIsString($snapshotRestoreModel->getRenameReplacement());

        $this->assertEquals($snapshotRestoreModel->getJson(), [
            'indices' => implode(',', $snapshotRestoreModel->getIndices()),
            'ignore_unavailable' => $snapshotRestoreModel->getIgnoreUnavailable(),
            'partial' => $snapshotRestoreModel->getPartial(),
            'include_global_state' => $snapshotRestoreModel->getIncludeGlobalState(),
            'rename_pattern' => $snapshotRestoreModel->getRenamePattern(),
            'rename_replacement' => $snapshotRestoreModel->getRenameReplacement(),
        ]);
        $this->assertIsArray($snapshotRestoreModel->getJson());
    }
}
