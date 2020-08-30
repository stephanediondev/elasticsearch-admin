<?php

namespace App\Tests\Manager;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class ElasticsearchSlmPolicyManagerTest extends WebTestCase
{
    public function testGetByNameNull()
    {
        self::bootKernel();
        $container = self::$kernel->getContainer();

        $elasticsearchSlmPolicyManager = self::$container->get('App\Manager\ElasticsearchSlmPolicyManager');

        $policy = $elasticsearchSlmPolicyManager->getByName(uniqid());

        $this->assertNull($policy);
    }
}
