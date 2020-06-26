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

        $callRequest = new CallRequestModel();
        $callRequest->setPath('/_xpack');
        $callResponse = $this->callManager->call($callRequest);
        $xpack = $callResponse->getContent();

        if (true == isset($xpack['features']['security']) && true == $xpack['features']['security']['enabled']) {
            // role
            $callRequest = new CallRequestModel();
            $callRequest->setMethod('GET');
            $callRequest->setPath('/_security/role/'.$names[0]);
            $callResponse = $this->callManager->call($callRequest);

            if (Response::HTTP_OK == $callResponse->getCode()) {
                $callRequest = new CallRequestModel();
                $callRequest->setMethod('DELETE');
                $callRequest->setPath('/_security/role/'.$names[0]);
                $this->callManager->call($callRequest);
            }

            $json = [
                'cluster' => [],
                'run_as' => [],
            ];
            $callRequest = new CallRequestModel();
            $callRequest->setMethod('POST');
            $callRequest->setJson($json);
            $callRequest->setPath('/_security/role/'.$names[0]);
            $this->callManager->call($callRequest);

            $output->writeln('<info>Role created: '.$names[0].'</info>');

            // user
            $callRequest = new CallRequestModel();
            $callRequest->setMethod('GET');
            $callRequest->setPath('/_security/user/'.$names[0]);
            $callResponse = $this->callManager->call($callRequest);

            if (Response::HTTP_OK == $callResponse->getCode()) {
                $callRequest = new CallRequestModel();
                $callRequest->setMethod('DELETE');
                $callRequest->setPath('/_security/user/'.$names[0]);
                $this->callManager->call($callRequest);
            }

            $json = [
                'password' => $names[0],
                'roles' => [$names[0]],
            ];
            $callRequest = new CallRequestModel();
            $callRequest->setMethod('POST');
            $callRequest->setJson($json);
            $callRequest->setPath('/_security/user/'.$names[0]);
            $this->callManager->call($callRequest);

            $output->writeln('<info>User created: '.$names[0].'</info>');
        }

        foreach ($names as $name) {
            $json = [
                'settings' => ['number_of_shards' => 1, 'auto_expand_replicas' => '0-1'],
                'mappings' => json_decode(file_get_contents(__DIR__.'/mappings.json'), true),
            ];

            // index
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

            $callRequest = new CallRequestModel();
            $callRequest->setMethod('PUT');
            $callRequest->setJson($json);
            $callRequest->setPath('/'.$name.'?include_type_name=false');
            $this->callManager->call($callRequest);

            $output->writeln('<info>Index created: '.$name.'</info>');

            // index template
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

            $json['index_patterns'] = $name;
            $callRequest = new CallRequestModel();
            $callRequest->setMethod('PUT');
            $callRequest->setJson($json);
            $callRequest->setPath('/_template/'.$name.'?include_type_name=false');
            $this->callManager->call($callRequest);

            $output->writeln('<info>Index template created: '.$name.'</info>');
        }

        return Command::SUCCESS;
    }
}
