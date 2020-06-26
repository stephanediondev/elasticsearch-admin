<?php

namespace App\Command;

use App\Manager\CallManager;
use App\Model\CallRequestModel;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\HttpFoundation\Response;

class PhpunitCommand extends Command
{
    protected static $defaultName = 'app:phpunit';

    public function __construct(CallManager $callManager)
    {
        $this->callManager = $callManager;

        parent::__construct();
    }

    protected function configure()
    {
        $this->setDescription('Create data for tests');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $names = ['elasticsearch-admin-test', '.elasticsearch-admin-test'];

        foreach ($names as $name) {
            $callRequest = new CallRequestModel();
            $callRequest->setMethod('HEAD');
            $callRequest->setPath('/'.$name);
            $callResponse = $this->callManager->call($callRequest);

            if (Response::HTTP_OK == $callResponse->getCode()) {
                $callRequest = new CallRequestModel();
                $callRequest->setMethod('DELETE');
                $callRequest->setPath('/'.$name);
                $this->callManager->call($callRequest);
            }

            $json = [
                'settings' => ['number_of_shards' => 1, 'auto_expand_replicas' => '0-1'],
                'mappings' => json_decode(file_get_contents(__DIR__.'/elasticsearch-admin-test.json'), true),
            ];
            $callRequest = new CallRequestModel();
            $callRequest->setMethod('PUT');
            $callRequest->setJson($json);
            $callRequest->setPath('/'.$name);
            $this->callManager->call($callRequest);

            $output->writeln('<info>Index created: '.$name.'</info>');
        }

        foreach ($names as $name) {
            $callRequest = new CallRequestModel();
            $callRequest->setMethod('HEAD');
            $callRequest->setPath('/_template/'.$name);
            $callResponse = $this->callManager->call($callRequest);

            if (Response::HTTP_OK == $callResponse->getCode()) {
                $callRequest = new CallRequestModel();
                $callRequest->setMethod('DELETE');
                $callRequest->setPath('/_template/'.$name);
                $this->callManager->call($callRequest);
            }

            $json = [
                'index_patterns' => $name,
                'settings' => ['number_of_shards' => 1, 'auto_expand_replicas' => '0-1'],
                'mappings' => json_decode(file_get_contents(__DIR__.'/elasticsearch-admin-test.json'), true),
            ];
            $callRequest = new CallRequestModel();
            $callRequest->setMethod('PUT');
            $callRequest->setJson($json);
            $callRequest->setPath('/_template/'.$name);
            $this->callManager->call($callRequest);

            $output->writeln('<info>Index template created: '.$name.'</info>');
        }

        return Command::SUCCESS;
    }
}
