<?php

namespace App\Tests\Manager;

use App\Model\ElasticsearchSlmPolicyModel;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class ElasticsearchSlmManagerTest extends WebTestCase
{
    public function testGetByName404()
    {
        self::bootKernel();
        $container = self::$kernel->getContainer();

        $elasticsearchSlmPolicyManager = self::$container->get('App\Manager\ElasticsearchSlmPolicyManager');

        $policy = $elasticsearchSlmPolicyManager->getByName(uniqid());

        $this->assertNull($policy);
    }
}
