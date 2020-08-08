<?php

namespace App\Tests\Model;

use App\Model\ElasticsearchReloadSecureSettingsModel;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class ElasticsearchReloadSecureSettingsModelTest extends WebTestCase
{
    public function test()
    {
        $reloadSecureSettings = new ElasticsearchReloadSecureSettingsModel();
        $reloadSecureSettings->setSecureSettingsPassword('secure-settings-password');

        $this->assertEquals($reloadSecureSettings->getSecureSettingsPassword(), 'secure-settings-password');
    }
}
