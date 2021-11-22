<?php

namespace App\Tests\Manager;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class ElasticsearchIlmManagerTest extends WebTestCase
{
    public function testGetByNameNull(): void
    {
        $elasticsearchIlmPolicyManager = static::getContainer()->get('App\Manager\ElasticsearchIlmPolicyManager');

        $policy = $elasticsearchIlmPolicyManager->getByName(uniqid());

        $this->assertNull($policy);
    }
}
