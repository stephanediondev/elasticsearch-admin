<?php

namespace App\Tests\Controller;

use App\Core\Traits\JwtTrait;
use App\Model\CallRequestModel;
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
        $callRequest->setPath('/_xpack');
        $callResponse = $this->callManager->call($callRequest);
        $this->xpack = $callResponse->getContent();

        $session = self::$container->get('session');

        $firewallName = 'main';

        $token = new UsernamePasswordToken(self::$container->getParameter('email'), null, $firewallName, ['ROLE_ADMIN']);
        $session->set('_security_'.$firewallName, serialize($token));
        $session->save();

        $cookie = new Cookie($session->getName(), $session->getId());
        $this->client->getCookieJar()->set($cookie);
    }
}
