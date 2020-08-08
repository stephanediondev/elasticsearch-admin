<?php

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
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class PhpunitCommand extends Command
{
    protected static $defaultName = 'app:phpunit';

    public function __construct(CallManager $callManager, AppManager $appManager, AppUserManager $appUserManager, UserPasswordEncoderInterface $passwordEncoder)
    {
        $this->callManager = $callManager;
        $this->appManager = $appManager;
        $this->appUserManager = $appUserManager;
        $this->passwordEncoder = $passwordEncoder;

        parent::__construct();
    }

    protected function configure()
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
            if (true == $this->callManager->checkVersion('7.0')) {
                $json['mappings'] = $this->appManager->getMappings('.elasticsearch-admin-users');
            }
            $callRequest = new CallRequestModel();
            $callRequest->setMethod('PUT');
            $callRequest->setJson($json);
            $callRequest->setPath('/.elasticsearch-admin-users-v'.$this->appManager->getVersion());
            $callResponse = $this->callManager->call($callRequest);

            $user = new AppUserModel();
            $user->setEmail('example@example.com');
            $user->setPassword($this->passwordEncoder->encodePassword($user, 'example'));
            $user->setRoles(['ROLE_ADMIN']);

            $callResponse = $this->appUserManager->send($user);

            sleep(2);
        }

        if (true == $this->callManager->hasFeature('_security_endpoint')) {
            $this->endpoint = '/_security';
        } else {
            $this->endpoint = '/_xpack/security';
        }

        $jsonIndex = [
            'settings' => ['index' => ['number_of_shards' => 1, 'auto_expand_replicas' => '0-1']],
        ];
        if (true == $this->callManager->checkVersion('7.0')) {
            $jsonIndex['mappings'] = json_decode(file_get_contents(__DIR__.'/../DataFixtures/es-test-mappings.json'), true);
        }

        $cases = [
            'elasticsearch_role' => [
                'name' => 'elasticsearch-admin-test',
                'path' => $this->endpoint.'/role',
                'feature' => 'security',
                'json' => ['cluster' => [], 'run_as' => []],
            ],
            'elasticsearch_user' => [
                'name' => 'elasticsearch-admin-test',
                'path' => $this->endpoint.'/user',
                'feature' => 'security',
                'json' => ['password' => uniqid(), 'roles' => ['elasticsearch-admin-test']],
            ],
            'index' => [
                'name' => 'elasticsearch-admin-test',
                'path' => '',
                'json' => $jsonIndex,
            ],
            'index_system' => [
                'name' => '.elasticsearch-admin-test',
                'path' => '',
                'json' => $jsonIndex,
            ],
            'index_template_legacy' => [
                'name' => 'elasticsearch-admin-test',
                'path' => '_template',
                'json' => true == $this->callManager->hasFeature('multiple_patterns') ? ['index_patterns' => 'elasticsearch-admin-test'] : ['template' => 'elasticsearch-admin-test'],
            ],
            'index_template_legacy_system' => [
                'name' => '.elasticsearch-admin-test',
                'path' => '_template',
                'json' => true == $this->callManager->hasFeature('multiple_patterns') ? ['index_patterns' => '.elasticsearch-admin-test'] : ['template' => '.elasticsearch-admin-test'],
            ],
            'index_template' => [
                'name' => 'elasticsearch-admin-test',
                'path' => '_index_template',
                'feature' => 'composable_template',
                'json' => ['index_patterns' => 'elasticsearch-admin-test'],
            ],
            'index_template_system' => [
                'name' => '.elasticsearch-admin-test',
                'path' => '_index_template',
                'feature' => 'composable_template',
                'json' => ['index_patterns' => '.elasticsearch-admin-test'],
            ],
            'component_template' => [
                'name' => 'elasticsearch-admin-test',
                'path' => '_component_template',
                'feature' => 'composable_template',
                'json' => ['template' => (object)[]],
            ],
            'component_template_system' => [
                'name' => '.elasticsearch-admin-test',
                'path' => '_component_template',
                'feature' => 'composable_template',
                'json' => ['template' => (object)[]],
            ],
            'pipeline' => [
                'name' => 'elasticsearch-admin-test',
                'path' => '_ingest/pipeline',
                'feature' => 'pipelines',
                'json' => ['processors' => []],
            ],
            'enrich' => [
                'name' => 'elasticsearch-admin-test',
                'path' => '_enrich/policy',
                'feature' => 'enrich',
                'json' => ['match' => ['indices' => 'elasticsearch-admin-test', 'match_field' => 'test-text', 'enrich_fields' => 'test-boolean']],
            ],
            'ilm_policy' => [
                'name' => 'elasticsearch-admin-test',
                'path' => '_ilm/policy',
                'feature' => 'ilm',
                'json' => ['policy' => ['phases' => ['delete' => ['min_age' => '7d', 'actions' => ['delete' => (object)[]]]]]],
            ],
            /*'slm_policy' => [
                'name' => 'elasticsearch-admin-test',
                'path' => '_slm/policy',
                'feature' => 'slm',
                'json' => ['name' => '<nightly-snap-{now/d}>', 'schedule' => '0 30 1 * * ?', 'repository' => 'fs'],
            ],
            'snapshot' => [
                'name' => 'elasticsearch-admin-test',
                'path' => '_snapshot/fs',
                'json' => ['indices' => ['elasticsearch-admin-test']],
            ],*/
        ];

        foreach ($cases as $case => $parameters) {
            if (true == isset($parameters['feature']) && false == $this->callManager->hasFeature($parameters['feature'])) {
                continue;
            }

            $callRequest = new CallRequestModel();
            $callRequest->setMethod('GET');
            $callRequest->setPath($parameters['path'].'/'.$parameters['name']);
            $callResponse = $this->callManager->call($callRequest);

            if (Response::HTTP_OK == $callResponse->getCode()) {
                if ('enrich' == $case) {
                    $content = $callResponse->getContent();
                    $policies = $content['policies'];
                    if (0 < count($policies)) {
                        continue;
                    }
                } else {
                    continue;
                }
            }

            $callRequest = new CallRequestModel();
            $callRequest->setMethod('PUT');
            if (true == isset($parameters['json'])) {
                $callRequest->setJson($parameters['json']);
            }
            $callRequest->setPath($parameters['path'].'/'.$parameters['name']);
            $this->callManager->call($callRequest);

            $output->writeln($case.' created: <info>'.$parameters['name'].'</info>');
        }

        $output->writeln('');

        return Command::SUCCESS;
    }
}
