<?php

namespace App\Tests\Manager;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class ElasticsearchEnrichPolicyManagerTest extends WebTestCase
{
    public function testGetByNameNull()
    {
        self::bootKernel();
        $container = self::$kernel->getContainer();

        $elasticsearchEnrichPolicyManager = self::$container->get('App\Manager\ElasticsearchEnrichPolicyManager');

        $policy = $elasticsearchEnrichPolicyManager->getByName(uniqid());

        $this->assertNull($policy);
    }
}
