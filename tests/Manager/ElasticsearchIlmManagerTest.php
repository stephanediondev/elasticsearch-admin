<?php

namespace App\Tests\Manager;

use App\Manager\ElasticsearchIlmPolicyManager;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class ElasticsearchIlmManagerTest extends WebTestCase
{
    public function testGetByNameNull(): void
    {
        /**
         * @var ElasticsearchIlmPolicyManager $elasticsearchIlmPolicyManager
         */
        $elasticsearchIlmPolicyManager = static::getContainer()->get('App\Manager\ElasticsearchIlmPolicyManager');

        $policy = $elasticsearchIlmPolicyManager->getByName(uniqid());

        $this->assertNull($policy);
    }
}
