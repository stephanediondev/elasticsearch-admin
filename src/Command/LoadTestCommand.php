<?php

namespace App\Command;

use App\Manager\CallManager;
use App\Model\CallRequestModel;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\Question;

class LoadTestCommand extends Command
{
    protected static $defaultName = 'app:load-test';

    public function __construct(CallManager $callManager)
    {
        $this->callManager = $callManager;

        parent::__construct();
    }

    protected function configure()
    {
        $this->setDescription('Add multiple indices for testing');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $output->writeln('<error>DO NOT USE IN PRODUCTION</error>');

        $helper = $this->getHelper('question');

        $question = new Question('Number of indices? ');
        $numberOfIndices = $helper->ask($input, $output, $question);

        $question = new Question('Number of shards by index? ');
        $numberOfShards = $helper->ask($input, $output, $question);

        $question = new Question('Number of replicas by index? ');
        $numberOfReplicas = $helper->ask($input, $output, $question);

        if (0 < $numberOfIndices) {
            $progressBar = new ProgressBar($output, $numberOfIndices);

            $progressBar->start();

            for ($i=1;$i<=$numberOfIndices;$i++) {
                $json = [
                    'settings' => [
                        'index.number_of_shards' => $numberOfShards ?? 1,
                        'index.number_of_replicas' => $numberOfReplicas ?? 0,
                    ],
                ];
                $callRequest = new CallRequestModel();
                $callRequest->setMethod('PUT');
                $callRequest->setJson($json);
                $callRequest->setPath('/load-test-'.uniqid('', true));
                $this->callManager->call($callRequest);

                $progressBar->advance();
            }

            $progressBar->finish();
        }

        return Command::SUCCESS;
    }
}
