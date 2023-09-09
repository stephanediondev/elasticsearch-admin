<?php

namespace App\Tests\Manager;

use App\Manager\ElasticsearchComponentTemplateManager;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class ElasticsearchComponentTemplateManagerTest extends WebTestCase
{
    public function testGetByNameNull(): void
    {
        $elasticsearchComponentTemplateManager = static::getContainer()->get('App\Manager\ElasticsearchComponentTemplateManager');

        if ($elasticsearchComponentTemplateManager instanceof ElasticsearchComponentTemplateManager) {
            $template = $elasticsearchComponentTemplateManager->getByName(uniqid());

            $this->assertNull($template);
        }
    }
}
