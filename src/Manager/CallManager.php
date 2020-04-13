<?php

namespace App\Manager;

use App\Exception\CallException;
use App\Model\CallRequestModel;
use App\Model\CallResponseModel;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class CallManager
{
    /**
     * @required
     */
    public function init(HttpClientInterface $client, string $elasticsearchUrl, string $elasticsearchUsername, string $elasticsearchPassword, bool $sslVerifyPeer)
    {
        $this->client = $client;
        $this->elasticsearchUrl = $elasticsearchUrl;
        $this->elasticsearchUsername = $elasticsearchUsername;
        $this->elasticsearchPassword = $elasticsearchPassword;
        $this->sslVerifyPeer = $sslVerifyPeer;
    }

    public function call(CallRequestModel $callRequest)
    {
        $options = $callRequest->getOptions();

        $headers = [];

        if (false == $options['body']) {
            unset($options['body']);
        } else {
            $headers['Content-Type'] = 'application/json; charset=UTF-8';
        }

        if (0 == count($options['json'])) {
            unset($options['json']);
        }

        if ('GET' == $callRequest->getMethod() && false == isset($options['query']['format'])) {
            $options['query']['format'] = 'json';
        }

        if ($this->elasticsearchUsername && $this->elasticsearchPassword) {
            $headers['Authorization'] = 'Basic '.base64_encode($this->elasticsearchUsername.':'.$this->elasticsearchPassword);
        }

        if (0 < count($headers)) {
            $options['headers'] = $headers;
        }

        $options['verify_peer'] = $this->sslVerifyPeer;

        $response = $this->client->request($callRequest->getMethod(), $this->elasticsearchUrl.$callRequest->getPath(), $options);

        $callResponse = new CallResponseModel();
        $callResponse->setCode($response->getStatusCode());

        if ($response && in_array($response->getStatusCode(), [400, 401, 405, 500])) {
            $json = json_decode($response->getContent(false), true);

            if (true == isset($json['error'])) {
                if (true == isset($json['error']['caused_by']) && true == isset($json['error']['caused_by']['reason'])) {
                    throw new CallException($json['error']['caused_by']['reason']);
                } elseif (true == isset($json['error']['reason'])) {
                    throw new CallException($json['error']['reason']);
                }
            }
            throw new CallException('Not found or method not allowed for '.$callRequest->getPath().' ('.$callRequest->getMethod().')');
        }

        if ($response && 'HEAD' != $callRequest->getMethod() && 404 != $response->getStatusCode()) {
            if (true == isset($options['query']['format']) && 'text' == $options['query']['format']) {
                $callResponse->setContentRaw($response->getContent());
            } else {
                $callResponse->setContent($response->toArray());
            }
        }

        return $callResponse;
    }
}
