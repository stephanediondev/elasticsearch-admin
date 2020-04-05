<?php

namespace App\Tests\Controller;

use App\Core\Traits\JwtTrait;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\BrowserKit\Cookie;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;

abstract class AbstractAppControllerTest extends WebTestCase
{
    protected $client;

    protected function setUp(): void
    {
        $this->client = static::createClient();

        $session = self::$container->get('session');

        $firewallName = 'main';

        $token = new UsernamePasswordToken(self::$container->getParameter('email'), null, $firewallName, ['ROLE_ADMIN']);
        $session->set('_security_'.$firewallName, serialize($token));
        $session->save();

        $cookie = new Cookie($session->getName(), $session->getId());
        $this->client->getCookieJar()->set($cookie);
    }
}
