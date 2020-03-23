<?php

namespace App\Manager;

use Symfony\Component\HttpClient\HttpClient;

class QueryManager
{
    /**
     * @required
     */
    public function init(string $elasticsearchUrl)
    {
        $this->elasticsearchUrl = $elasticsearchUrl;
        $this->client = HttpClient::create();
    }

    public function query(string $method, string $path, array $query = []): array
    {
        $query['query']['format'] = 'json';
        $response = $this->client->request($method, $this->elasticsearchUrl.$path, $query);
        $content = $response->toArray();

        return $content;
    }
}
