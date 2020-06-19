<?php

namespace App\Model;

use App\Model\AbstractAppModel;

class ElasticsearchReloadSecureSettingsModel extends AbstractAppModel
{
    private $secureSettingsPassword;

    public function __construct()
    {
        $this->secureSettingsPassword = '';
    }

    public function getSecureSettingsPassword(): ?string
    {
        return $this->secureSettingsPassword;
    }

    public function setSecureSettingsPassword(?string $secureSettingsPassword): self
    {
        $this->secureSettingsPassword = $secureSettingsPassword;

        return $this;
    }
}
