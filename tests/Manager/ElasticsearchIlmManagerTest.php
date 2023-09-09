<?php

namespace App\Tests\Manager;

use App\Manager\ElasticsearchIlmPolicyManager;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class ElasticsearchIlmManagerTest extends WebTestCase
{
    public function testGetByNameNull(): void
    {
        $elasticsearchIlmPolicyManager = static::getContainer()->get('App\Manager\ElasticsearchIlmPolicyManager');

        if ($elasticsearchIlmPolicyManager instanceof ElasticsearchIlmPolicyManager) {
            $policy = $elasticsearchIlmPolicyManager->getByName(uniqid());

            $this->assertNull($policy);
        }
    }
}
