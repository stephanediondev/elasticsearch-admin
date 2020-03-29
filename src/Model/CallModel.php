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
        $this->options = ['query' => [], 'body' => []];
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
        if ('/' != substr($path, 0, 1)) {
            $path = '/'.$path;
        }
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

    public function getBody(): ?array
    {
        return $this->options['body'];
    }

    public function setBody(?array $body): self
    {
        $this->options['body'] = $body;

        return $this;
    }

    public function getBodyJson(): ?string
    {
        return json_encode($this->options['body'], true);
    }

    public function setBodyJson(?string $body): self
    {
        $this->options['body'] = json_decode($body);

        return $this;
    }

    public static function getMethods(): ?array
    {
        return [
            'GET' => 'GET',
            'POST' => 'POST',
            'PUT' => 'PUT',
            'DELETE' => 'DELETE',
            'HEAD' => 'HEAD',
        ];
    }
}
