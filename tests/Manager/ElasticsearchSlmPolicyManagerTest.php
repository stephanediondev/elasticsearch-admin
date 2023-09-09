<?php

namespace App\Tests\Manager;

use App\Manager\ElasticsearchSlmPolicyManager;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class ElasticsearchSlmPolicyManagerTest extends WebTestCase
{
    public function testGetByNameNull(): void
    {
        /**
         * @var ElasticsearchSlmPolicyManager $elasticsearchSlmPolicyManager
         */
        $elasticsearchSlmPolicyManager = static::getContainer()->get('App\Manager\ElasticsearchSlmPolicyManager');

        $policy = $elasticsearchSlmPolicyManager->getByName(uniqid());

        $this->assertNull($policy);
    }
}
