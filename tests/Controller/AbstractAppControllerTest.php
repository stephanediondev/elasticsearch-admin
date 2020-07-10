<?php

namespace App\Tests\Controller;

use App\Core\Traits\JwtTrait;
use App\Model\CallRequestModel;
use App\Security\User;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\BrowserKit\Cookie;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;

abstract class AbstractAppControllerTest extends WebTestCase
{
    protected $client;

    protected $callManager;

    protected $xpack;

    protected function setUp(): void
    {
        $this->client = static::createClient();

        $this->callManager = self::$container->get('App\Manager\CallManager');

        $callRequest = new CallRequestModel();
        $callRequest->setPath('/');
        $callResponse = $this->callManager->call($callRequest);
        $this->root = $callResponse->getContent();

        $callRequest = new CallRequestModel();
        $callRequest->setPath('/_xpack');
        $callResponse = $this->callManager->call($callRequest);
        $this->xpack = $callResponse->getContent();

        $callRequest = new CallRequestModel();
        $callRequest->setPath('/_cat/plugins');
        $callResponse = $this->callManager->call($callRequest);
        $results = $callResponse->getContent();

        $this->plugins = [];
        foreach ($results as $row) {
            $this->plugins[] = $row['component'];
        }

        $session = self::$container->get('session');

        $firewallName = 'main';

        $query = [
            'q' => 'email:"example@example.com"',
        ];
        $callRequest = new CallRequestModel();
        $callRequest->setPath('/.elastictsearch-admin-users/_search');
        $callRequest->setQuery($query);
        $callResponse = $this->callManager->call($callRequest);
        $results = $callResponse->getContent();

        if (1 == count($results['hits']['hits'])) {
            foreach ($results['hits']['hits'] as $row) {
                $row = $row['_source'];

                $user = new User();
                $user->setEmail($row['email']);
                $user->setPassword($row['password']);
                $user->setRoles($row['roles']);
            }
        }

        $this->client->loginUser($user);
    }

    protected function checkVersion(string $versionGoal): bool
    {
        if (true == isset($this->root['version']) && true == isset($this->root['version']['number']) && 0 <= version_compare($this->root['version']['number'], $versionGoal)) {
            return true;
        }

        return false;
    }

    protected function hasFeature(string $feature): bool
    {
        if (true == isset($this->xpack['features'][$feature]) && true == $this->xpack['features'][$feature]['available'] && true == $this->xpack['features'][$feature]['enabled']) {
            return true;
        }

        return false;
    }

    protected function hasPlugin(string $plugin): bool
    {
        if (true == in_array($plugin, $this->plugins)) {
            return true;
        }

        return false;
    }
}
