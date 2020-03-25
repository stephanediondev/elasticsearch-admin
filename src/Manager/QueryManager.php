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

    public function query(string $method, string $path, array $options = [])
    {
        if (true == isset($options['body'])) {
            $options['body'] = json_encode($options['body']);
            $options['headers'] = [
                'Content-Type' => 'application/json; charset=UTF-8',
            ];
        }

        if ('GET' == $method && false == isset($options['query']['format'])) {
            $options['query']['format'] = 'json';
        }

        $response = $this->client->request($method, $this->elasticsearchUrl.$path, $options);

        if (true == isset($options['query']['format']) && 'json' == $options['query']['format']) {
            return $response->toArray();
        } else {
            return $response->getContent();
        }
    }
}
