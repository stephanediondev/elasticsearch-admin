<?php

declare(strict_types=1);

namespace App\Model;

use App\Model\AbstractAppModel;

class ElasticsearchReloadSecureSettingsModel extends AbstractAppModel
{
    private ?string $secureSettingsPassword = null;

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

    public function getJson(): array
    {
        $json = [
            'secure_settings_password' => $this->getSecureSettingsPassword() ? $this->getSecureSettingsPassword() : '',
        ];

        return $json;
    }
}
