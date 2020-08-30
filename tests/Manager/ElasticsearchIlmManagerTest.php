<?php

namespace App\Tests\Manager;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class ElasticsearchIlmManagerTest extends WebTestCase
{
    public function testGetByNameNull()
    {
        self::bootKernel();
        $container = self::$kernel->getContainer();

        $elasticsearchIlmPolicyManager = self::$container->get('App\Manager\ElasticsearchIlmPolicyManager');

        $policy = $elasticsearchIlmPolicyManager->getByName(uniqid());

        $this->assertNull($policy);
    }
}
