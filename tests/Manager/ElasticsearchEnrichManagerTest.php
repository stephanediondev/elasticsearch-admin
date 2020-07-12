<?php

namespace App\Tests\Manager;

use App\Model\ElasticsearchEnrichPolicyModel;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class ElasticsearchEnrichPolicyManagerTest extends WebTestCase
{
    public function testGetByName404()
    {
        self::bootKernel();
        $container = self::$kernel->getContainer();

        $elasticsearchEnrichPolicyManager = self::$container->get('App\Manager\ElasticsearchEnrichPolicyManager');

        $policy = $elasticsearchEnrichPolicyManager->getByName(uniqid());

        $this->assertNull($policy);
    }
}
