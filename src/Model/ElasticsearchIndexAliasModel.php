<?php
declare(strict_types=1);

namespace App\Model;

use App\Model\AbstractAppModel;

class ElasticsearchIndexAliasModel extends AbstractAppModel
{
    private ?string $name = null;

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(?string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function __toString(): string
    {
        return $this->name;
    }
}
