<?php

namespace App\Manager;

use App\Exception\CallException;
use App\Model\CallRequestModel;
use App\Model\CallResponseModel;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class CallManager
{
    public $root = false;

    public $xpack = false;

    public $plugins = false;

    public function __construct(HttpClientInterface $client, string $elasticsearchUrl, string $elasticsearchUsername, string $elasticsearchPassword, bool $sslVerifyPeer)
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

    public function getRoot(): array
    {
        if (false == $this->root) {
            $this->setRoot();
        }

        return $this->root;
    }

    public function setRoot()
    {
        $callRequest = new CallRequestModel();
        $callRequest->setPath('/');
        $callResponse = $this->call($callRequest);
        $this->root = $callResponse->getContent();
    }

    public function getXpack(): array
    {
        if (false == $this->xpack) {
            $this->setXpack();
        }

        return $this->xpack;
    }

    public function setXpack()
    {
        $callRequest = new CallRequestModel();
        $callRequest->setPath('/_xpack');
        $callResponse = $this->call($callRequest);
        $this->xpack = $callResponse->getContent();
    }

    public function getPlugins(): array
    {
        if (false == $this->plugins) {
            $this->setPlugins();
        }

        return $this->plugins;
    }

    public function setPlugins()
    {
        $callRequest = new CallRequestModel();
        $callRequest->setPath('/_cat/plugins');
        $callResponse = $this->call($callRequest);
        $results = $callResponse->getContent();

        $this->plugins = [];
        foreach ($results as $row) {
            $this->plugins[] = $row['component'];
        }
    }

    public function checkVersion(string $versionGoal): bool
    {
        if (false == $this->root) {
            $this->setRoot();
        }

        if (true == isset($this->root['version']) && true == isset($this->root['version']['number']) && 0 <= version_compare($this->root['version']['number'], $versionGoal)) {
            return true;
        }

        return false;
    }

    public function hasFeature(string $feature): bool
    {
        if (false == $this->xpack) {
            $this->setXpack();
        }

        if (true == isset($this->xpack['features'][$feature]) && true == $this->xpack['features'][$feature]['available'] && true == $this->xpack['features'][$feature]['enabled']) {
            return true;
        }

        return false;
    }

    public function hasPlugin(string $plugin): bool
    {
        if (false == $this->plugins) {
            $this->setPlugins();
        }

        if (true == in_array($plugin, $this->plugins)) {
            return true;
        }

        return false;
    }
}
