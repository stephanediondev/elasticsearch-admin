<?php

namespace App\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Yaml\Yaml;

class TranslationsForLaravelCommand extends Command
{
    protected static $defaultName = 'app:translations-for-laravel';

    protected function configure()
    {
        $this->setDescription('Routes converted for Laravel');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $yaml = Yaml::parseFile(__DIR__.'/../../translations/messages.en.yaml');
        var_export($yaml);

        return 0;
    }
}
