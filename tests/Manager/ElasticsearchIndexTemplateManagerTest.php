<?php

namespace App\Tests\Manager;

use App\Manager\ElasticsearchIndexTemplateManager;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class ElasticsearchIndexTemplateManagerTest extends WebTestCase
{
    public function testGetByNameNull(): void
    {
        $elasticsearchIndexTemplateManager = static::getContainer()->get('App\Manager\ElasticsearchIndexTemplateManager');

        if ($elasticsearchIndexTemplateManager instanceof ElasticsearchIndexTemplateManager) {
            $template = $elasticsearchIndexTemplateManager->getByName(uniqid());

            $this->assertNull($template);
        }
    }
}
