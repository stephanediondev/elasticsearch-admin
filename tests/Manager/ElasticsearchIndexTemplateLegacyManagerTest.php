<?php

namespace App\Tests\Manager;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class ElasticsearchIndexTemplateLegacyManagerTest extends WebTestCase
{
    public function testGetByNameNull(): void
    {
        $elasticsearchIndexTemplateLegacyManager = static::getContainer()->get('App\Manager\ElasticsearchIndexTemplateLegacyManager');

        $template = $elasticsearchIndexTemplateLegacyManager->getByName(uniqid());

        $this->assertNull($template);
    }
}
