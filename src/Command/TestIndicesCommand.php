<?php

namespace App\Command;

use App\Manager\CallManager;
use App\Model\CallRequestModel;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\HttpFoundation\Response;

class TestIndicesCommand extends Command
{
    protected static $defaultName = 'app:test-indices';

    public function __construct(CallManager $callManager)
    {
        $this->callManager = $callManager;

        parent::__construct();
    }

    protected function configure()
    {
        $this->setDescription('Create indices for tests');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $indices = ['elasticsearch-admin-test', '.elasticsearch-admin-test'];

        foreach ($indices as $index) {
            $callRequest = new CallRequestModel();
            $callRequest->setMethod('HEAD');
            $callRequest->setPath('/'.$index);
            $callResponse = $this->callManager->call($callRequest);

            if (Response::HTTP_OK == $callResponse->getCode()) {
                $callRequest = new CallRequestModel();
                $callRequest->setMethod('DELETE');
                $callRequest->setPath('/'.$index);
                $this->callManager->call($callRequest);
            }

            $json = [
                'settings' => ['number_of_shards' => 1, 'auto_expand_replicas' => '0-1'],
                'mappings' => json_decode(file_get_contents(__DIR__.'/elasticsearch-admin-test.json'), true),
            ];
            $callRequest = new CallRequestModel();
            $callRequest->setMethod('PUT');
            $callRequest->setJson($json);
            $callRequest->setPath('/'.$index);
            $this->callManager->call($callRequest);
        }

        return Command::SUCCESS;
    }
}
