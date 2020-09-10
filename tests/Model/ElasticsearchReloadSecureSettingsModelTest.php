<?php

namespace App\Tests\Model;

use App\Model\ElasticsearchReloadSecureSettingsModel;
use PHPUnit\Framework\TestCase;

class ElasticsearchReloadSecureSettingsModelTest extends TestCase
{
    public function test()
    {
        $reloadSecureSettings = new ElasticsearchReloadSecureSettingsModel();
        $reloadSecureSettings->setSecureSettingsPassword('secure-settings-password');

        $this->assertEquals($reloadSecureSettings->getSecureSettingsPassword(), 'secure-settings-password');
    }
}
