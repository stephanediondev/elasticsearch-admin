<?php

namespace App\Tests\Manager;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class ElasticsearchIlmManagerTest extends WebTestCase
{
    public function testGetByNameNull(): void
    {
        self::bootKernel();
        $container = self::$kernel->getContainer();

        $elasticsearchIlmPolicyManager = static::getContainer()->get('App\Manager\ElasticsearchIlmPolicyManager');

        $policy = $elasticsearchIlmPolicyManager->getByName(uniqid());

        $this->assertNull($policy);
    }
}
