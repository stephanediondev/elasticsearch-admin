<?php

namespace App\Model;

use App\Model\AbstractAppModel;

class CallResponseModel extends AbstractAppModel
{
    private $code;

    private $content;

    private $contentRaw;

    public function getCode(): ?int
    {
        return $this->code;
    }

    public function setCode(?int $code): self
    {
        $this->code = $code;

        return $this;
    }

    public function getContent(): ?array
    {
        return $this->content;
    }

    public function setContent(?array $content): self
    {
        $this->content = $content;

        return $this;
    }

    public function getContentRaw(): ?string
    {
        return $this->contentRaw;
    }

    public function setContentRaw(?string $contentRaw): self
    {
        $this->contentRaw = $contentRaw;

        return $this;
    }
}
