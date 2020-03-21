<?php

namespace App\Manager;

use Symfony\Component\HttpClient\HttpClient;

class QueryManager
{
    const BASE_URI = 'http://192.168.1.93:9200';

    /**
     * @required
     */
    public function init()
    {
        $this->client = HttpClient::create();
    }

    public function query(string $method, string $path, array $query = []): array
    {
        $query['format'] = 'json';
        $response = $this->client->request($method, self::BASE_URI.$path, ['query' => $query]);
        $content = $response->toArray();

        return $content;
    }
}
