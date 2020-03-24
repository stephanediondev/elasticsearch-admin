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

    public function query(string $method, string $path, array $query = [])
    {
        if (false == isset($query['query']['format'])) {
            $query['query']['format'] = 'json';
        }
        $response = $this->client->request($method, $this->elasticsearchUrl.$path, $query);
        if ('json' == $query['query']['format']) {
            return $response->toArray();
        } else {
            return $response->getContent();
        }
    }
}
