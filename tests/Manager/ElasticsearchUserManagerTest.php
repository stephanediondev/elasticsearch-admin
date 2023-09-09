<?php

namespace App\Tests\Manager;

use App\Manager\ElasticsearchUserManager;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class ElasticsearchUserManagerTest extends WebTestCase
{
    public function testGetByNameNull(): void
    {
        $elasticsearchUserManager = static::getContainer()->get('App\Manager\ElasticsearchUserManager');

        if ($elasticsearchUserManager instanceof ElasticsearchUserManager) {
            $user = $elasticsearchUserManager->getByName(uniqid());

            $this->assertNull($user);
        }
    }
}
