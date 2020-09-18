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
        $this->client = static::createClient([], ['HTTPS' => true]);

        $this->callManager = self::$container->get('App\Manager\CallManager');

        $this->elasticsearchClusterManager = self::$container->get('App\Manager\ElasticsearchClusterManager');

        $session = self::$container->get('session');

        $firewallName = 'main';

        $query = [
            'q' => 'email:"example@example.com"',
        ];
        $callRequest = new CallRequestModel();
        $callRequest->setPath('/.elasticsearch-admin-users/_search');
        $callRequest->setQuery($query);
        $callResponse = $this->callManager->call($callRequest);
        $results = $callResponse->getContent();

        if (1 == count($results['hits']['hits'])) {
            foreach ($results['hits']['hits'] as $row) {
                $user = new AppUserModel();
                $user->setId($row['_id']);
                $user->setEmail($row['_source']['email']);
                $user->setPassword($row['_source']['password']);
                $user->setRoles($row['_source']['roles']);
                if (true === isset($row['_source']['created_at']) && '' != $row['_source']['created_at']) {
                    $user->setCreatedAt(new \Datetime($row['_source']['created_at']));
                }
            }
        }

        $this->client->loginUser($user);
    }
}
