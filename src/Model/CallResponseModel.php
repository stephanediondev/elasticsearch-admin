<?php

namespace App\Model;

use App\Model\AbstractAppModel;

class CallResponseModel extends AbstractAppModel
{
    private $code;

    private $content;

    private $contentRaw;

    private $error;

    public function getCode(): ?string
    {
        return $this->code;
    }

    public function setCode(?string $code): self
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

    public function getError(): ?string
    {
        return $this->error;
    }

    public function setError(?string $error): self
    {
        $this->error;

        return $this;
    }
}
