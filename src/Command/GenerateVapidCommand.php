<?php

namespace App\Command;

use Minishlink\WebPush\VAPID;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class GenerateVapidCommand extends Command
{
    protected static $defaultName = 'app:generate-vapid';

    protected function configure(): void
    {
        $this->setDescription('Generate VAPID keys');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $output->writeln('<info>'.$this->getDescription().'</info>');

        $vapid = VAPID::createVapidKeys();

        $output->writeln('VAPID_PUBLIC_KEY=\''.$vapid['publicKey'].'\'');
        $output->writeln('VAPID_PRIVATE_KEY=\''.$vapid['privateKey'].'\'');

        return Command::SUCCESS;
    }
}
