<?php

namespace App\Tests\Controller;

use App\Core\Traits\JwtTrait;
use App\Manager\AppUserManager;
use App\Manager\CallManager;
use App\Manager\ElasticsearchClusterManager;
use App\Manager\ElasticsearchNodeManager;
use App\Model\AppUserModel;
use App\Model\CallRequestModel;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\BrowserKit\Cookie;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;

abstract class AbstractAppControllerTest extends WebTestCase
{
    protected KernelBrowser $client;

    protected CallManager $callManager;

    protected AppUserManager $appUserManager;

    protected ElasticsearchClusterManager $elasticsearchClusterManager;

    protected ElasticsearchNodeManager $elasticsearchNodeManager;

    public static function setUpBeforeClass(): void
    {
        if (false === defined('GENERATED_NAME')) {
            define('GENERATED_NAME', 'phpunit-'.uniqid());
        }

        if (false === defined('GENERATED_NAME_SYSTEM')) {
            define('GENERATED_NAME_SYSTEM', '.phpunit-'.uniqid());
        }

        if (false === defined('GENERATED_NAME_UPPER')) {
            define('GENERATED_NAME_UPPER', getRandomString(8));
        }

        if (false === defined('GENERATED_EMAIL')) {
            define('GENERATED_EMAIL', 'phpunit-'.uniqid().'@test.com');
        }
    }

    protected function setUp(): void
    {
        $this->client = static::createClient([], ['HTTPS' => true]);

        $this->callManager = static::getContainer()->get('App\Manager\CallManager');

        $this->appUserManager = static::getContainer()->get('App\Manager\AppUserManager');

        $this->elasticsearchClusterManager = static::getContainer()->get('App\Manager\ElasticsearchClusterManager');

        $this->elasticsearchNodeManager = static::getContainer()->get('App\Manager\ElasticsearchNodeManager');

        $firewallName = 'main';

        $query = [
            'q' => 'email:"example@example.com"',
        ];
        $callRequest = new CallRequestModel();
        $callRequest->setPath('/.elasticsearch-admin-users/_search');
        $callRequest->setQuery($query);
        $callResponse = $this->callManager->call($callRequest);
        $results = $callResponse->getContent();

        $user = null;

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

        if ($user) {
            $this->client->loginUser($user);
        }
    }
}

function getRandomString(int $length = 8): string
{
    $characters = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $string = '';

    for ($i = 0; $i < $length; $i++) {
        $string .= $characters[mt_rand(0, strlen($characters) - 1)];
    }

    return $string;
}
