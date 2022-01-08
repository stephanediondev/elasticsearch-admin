<?php
declare(strict_types=1);

namespace App\Command;

use App\Kernel;
use App\Manager\AppManager;
use App\Manager\AppUserManager;
use App\Manager\CallManager;
use App\Model\AppUserModel;
use App\Model\CallRequestModel;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class PhpunitCommand extends Command
{
    protected static $defaultName = 'app:phpunit';

    private CallManager $callManager;

    private AppManager $appManager;

    private AppUserManager $appUserManager;

    private UserPasswordHasherInterface $passwordHasher;

    public function __construct(CallManager $callManager, AppManager $appManager, AppUserManager $appUserManager, UserPasswordHasherInterface $passwordHasher)
    {
        $this->callManager = $callManager;
        $this->appManager = $appManager;
        $this->appUserManager = $appUserManager;
        $this->passwordHasher = $passwordHasher;

        parent::__construct();
    }

    protected function configure(): void
    {
        $this->setDescription('Create data for tests');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $output->writeln('PHP version: <info>'.phpversion().'</info>');

        $output->writeln('Symfony version: <info>'.Kernel::VERSION.'</info>');

        $output->writeln('Elasticsearch version: <info>'.$this->callManager->getRoot()['version']['number'].'</info>');

        $output->writeln('');

        $callRequest = new CallRequestModel();
        $callRequest->setMethod('HEAD');
        $callRequest->setPath('/.elasticsearch-admin-users');
        $callResponse = $this->callManager->call($callRequest);

        if (Response::HTTP_NOT_FOUND == $callResponse->getCode()) {
            $json = [
                'settings' => $this->appManager->getSettings('.elasticsearch-admin-users'),
                'aliases' => ['.elasticsearch-admin-users' => (object)[]],
            ];
            if (true === $this->callManager->checkVersion('7.0')) {
                $json['mappings'] = $this->appManager->getMappings('.elasticsearch-admin-users');
            }
            $callRequest = new CallRequestModel();
            $callRequest->setMethod('PUT');
            $callRequest->setJson($json);
            $callRequest->setPath('/.elasticsearch-admin-users-v'.$this->appManager->getVersion());
            $this->callManager->call($callRequest);

            $user = new AppUserModel();
            $user->setEmail('example@example.com');
            $user->setPassword($this->passwordHasher->hashPassword($user, 'example'));
            $user->setRoles(['ROLE_ADMIN']);

            $this->appUserManager->send($user);
        }

        $jsonIndex = [
            'settings' => ['index' => ['number_of_shards' => 1, 'auto_expand_replicas' => '0-1']],
        ];
        if (true === $this->callManager->checkVersion('7.0')) {
            if ($jsonMappings = file_get_contents(__DIR__.'/../DataFixtures/es-test-mappings.json')) {
                $jsonIndex['mappings'] = json_decode($jsonMappings, true);
            }
        }

        $cases = [
            'index' => [
                'name' => 'elasticsearch-admin-test',
                'path' => '',
                'json' => $jsonIndex,
            ],
            'enrich' => [
                'name' => 'elasticsearch-admin-test',
                'path' => '_enrich/policy',
                'feature' => 'enrich',
                'json' => ['match' => ['indices' => 'elasticsearch-admin-test', 'match_field' => 'test-text', 'enrich_fields' => 'test-boolean']],
            ],
        ];

        foreach ($cases as $case => $parameters) {
            if (true === isset($parameters['feature']) && false === $this->callManager->hasFeature($parameters['feature'])) {
                continue;
            }

            $callRequest = new CallRequestModel();
            $callRequest->setMethod('GET');
            $callRequest->setPath($parameters['path'].'/'.$parameters['name']);
            $callResponse = $this->callManager->call($callRequest);

            if (Response::HTTP_OK == $callResponse->getCode()) {
                if ('enrich' == $case) {
                    $content = $callResponse->getContent();
                    $policies = $content['policies'] ?? false;
                    if ($policies && 0 < count($policies)) {
                        continue;
                    }
                } else {
                    continue;
                }
            }

            $callRequest = new CallRequestModel();
            $callRequest->setMethod('PUT');
            $callRequest->setJson($parameters['json']);
            $callRequest->setPath($parameters['path'].'/'.$parameters['name']);
            $this->callManager->call($callRequest);

            $output->writeln($case.' created: <info>'.$parameters['name'].'</info>');
        }

        $output->writeln('');

        return Command::SUCCESS;
    }
}
