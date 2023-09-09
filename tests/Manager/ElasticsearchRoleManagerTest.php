<?php

namespace App\Tests\Manager;

use App\Manager\ElasticsearchRoleManager;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class ElasticsearchRoleManagerTest extends WebTestCase
{
    public function testGetByNameNull(): void
    {
        $elasticsearchRoleManager = static::getContainer()->get('App\Manager\ElasticsearchRoleManager');

        if ($elasticsearchRoleManager instanceof ElasticsearchRoleManager) {
            $role = $elasticsearchRoleManager->getByName(uniqid());

            $this->assertNull($role);
        }
    }
}
