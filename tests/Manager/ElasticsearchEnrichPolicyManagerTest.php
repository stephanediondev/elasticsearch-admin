<?php

namespace App\Tests\Manager;

use App\Manager\ElasticsearchEnrichPolicyManager;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class ElasticsearchEnrichPolicyManagerTest extends WebTestCase
{
    public function testGetByNameNull(): void
    {
        $elasticsearchEnrichPolicyManager = static::getContainer()->get('App\Manager\ElasticsearchEnrichPolicyManager');

        if ($elasticsearchEnrichPolicyManager instanceof ElasticsearchEnrichPolicyManager) {
            $policy = $elasticsearchEnrichPolicyManager->getByName(uniqid());

            $this->assertNull($policy);
        }
    }
}
