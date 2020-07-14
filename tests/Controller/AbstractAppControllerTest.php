<?php

namespace App\Tests\Controller;

use App\Core\Traits\JwtTrait;
use App\Model\CallRequestModel;
use App\Model\AppUserModel;
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

                $user = new AppUserModel();
                $user->setEmail($row['email']);
                $user->setPassword($row['password']);
                $user->setRoles($row['roles']);
            }
        }

        $this->client->loginUser($user);
    }
}
