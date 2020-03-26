<?php

namespace App\Model;

class CallModel
{
    private $method;

    private $path;

    private $options;

    public function __construct()
    {
        $this->method = 'GET';
        $this->options = [];
    }

    public function getMethod(): ?string
    {
        return $this->method;
    }

    public function setMethod(?string $method): self
    {
        $this->method = $method;

        return $this;
    }

    public function getPath(): ?string
    {
        return $this->path;
    }

    public function setPath(?string $path): self
    {
        $this->path = $path;

        return $this;
    }

    public function getOptions(): ?array
    {
        return $this->options;
    }

    public function setOptions(?array $options): self
    {
        $this->options = $options;

        return $this;
    }

    public function setQuery(?array $query): self
    {
        $this->options['query'] = $query;

        return $this;
    }

    public function setHeaders(?array $headers): self
    {
        $this->options['headers'] = $headers;

        return $this;
    }

    public function setBody(?array $body): self
    {
        $this->options['body'] = $body;

        return $this;
    }
}
