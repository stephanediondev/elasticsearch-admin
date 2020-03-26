<?php

namespace App\Manager;

use App\Exception\CallException;
use App\Model\CallModel;
use Symfony\Component\HttpClient\HttpClient;

class CallManager
{
    /**
     * @required
     */
    public function init(string $elasticsearchUrl, string $elasticsearchUsername, string $elasticsearchPassword)
    {
        $this->elasticsearchUrl = $elasticsearchUrl;
        $this->elasticsearchUsername = $elasticsearchUsername;
        $this->elasticsearchPassword = $elasticsearchPassword;
        $this->client = HttpClient::create();
    }

    public function call(CallModel $call)
    {
        $options = $call->getOptions();

        $headers = [];

        if (true == isset($options['body'])) {
            $options['body'] = json_encode($options['body']);
            $headers['Content-Type'] = 'application/json; charset=UTF-8';
        }

        if ('GET' == $call->getMethod() && false == isset($options['query']['format'])) {
            $options['query']['format'] = 'json';
        }

        if ($this->elasticsearchUsername && $this->elasticsearchPassword) {
            $headers['Authorization'] = 'Basic '.base64_encode($this->elasticsearchUsername.':'.$this->elasticsearchPassword);
        }

        if (0 < count($headers)) {
            $options['headers'] = $headers;
        }

        $response = $this->client->request($call->getMethod(), $this->elasticsearchUrl.$call->getPath(), $options);

        if ($response && in_array($response->getStatusCode(), [400, 404, 500])) {
            $json = json_decode($response->getContent(false), true);
            if (true == isset($json['error']) && true == isset($json['error']['reason'])) {
                throw new CallException($json['error']['reason']);
            }
        }

        if (true == isset($options['query']['format']) && 'text' == $options['query']['format']) {
            return $response->getContent();
        } else {
            return $response->toArray();
        }
    }
}
