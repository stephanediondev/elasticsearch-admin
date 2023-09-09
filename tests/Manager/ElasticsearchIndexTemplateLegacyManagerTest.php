<?php

namespace App\Tests\Manager;

use App\Manager\ElasticsearchIndexTemplateLegacyManager;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class ElasticsearchIndexTemplateLegacyManagerTest extends WebTestCase
{
    public function testGetByNameNull(): void
    {
        $elasticsearchIndexTemplateLegacyManager = static::getContainer()->get('App\Manager\ElasticsearchIndexTemplateLegacyManager');

        if ($elasticsearchIndexTemplateLegacyManager instanceof ElasticsearchIndexTemplateLegacyManager) {
            $template = $elasticsearchIndexTemplateLegacyManager->getByName(uniqid());

            $this->assertNull($template);
        }
    }
}
