<?php

namespace App\Tests\Manager;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class ElasticsearchSlmPolicyManagerTest extends WebTestCase
{
    public function testGetByNameNull(): void
    {
        $elasticsearchSlmPolicyManager = static::getContainer()->get('App\Manager\ElasticsearchSlmPolicyManager');

        $policy = $elasticsearchSlmPolicyManager->getByName(uniqid());

        $this->assertNull($policy);
    }
}
