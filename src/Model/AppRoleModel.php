<?php

namespace App\Model;

use App\Model\AbstractAppModel;

class AppRoleModel extends AbstractAppModel
{
    private $name;

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(?string $name): self
    {
        $this->name = $name;

        return $this;
    }
}
