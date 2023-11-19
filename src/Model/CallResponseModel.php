<?php

declare(strict_types=1);

namespace App\Model;

use App\Model\AbstractAppModel;

class CallResponseModel extends AbstractAppModel
{
    private ?int $code = null;

    /**
     * @var array<mixed>|null $content
     */
    private ?array $content = null;

    private ?string $contentRaw = null;

    public function getCode(): ?int
    {
        return $this->code;
    }

    public function setCode(?int $code): self
    {
        $this->code = $code;

        return $this;
    }

    /**
     * @return array<mixed>|null
     */
    public function getContent(): ?array
    {
        return $this->content;
    }

    /**
     * @param array<mixed>|null $content
     */
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
