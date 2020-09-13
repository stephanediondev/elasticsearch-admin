<?php

namespace App\Command;

use App\Kernel;
use App\Manager\AppManager;
use App\Manager\CallManager;
use App\Model\CallRequestModel;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\Question;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Contracts\Translation\TranslatorInterface;

class AppUninstallCommand extends Command
{
    protected static $defaultName = 'app:uninstall';

    public function __construct(AppManager $appManager, CallManager $callManager, TranslatorInterface $translator)
    {
        $this->appManager = $appManager;
        $this->callManager = $callManager;
        $this->translator = $translator;

        parent::__construct();
    }

    protected function configure()
    {
        $this->setDescription('Uninstall application');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $indices = $this->appManager->getIndices();

        $output->writeln('<error>'.$this->translator->trans('app_uninstall_note').'</error>');
        foreach ($indices as $index) {
            $output->writeln($index);
        }

        $helper = $this->getHelper('question');

        $question = new Question('Confirm this action with "yes" ');
        $answer = $helper->ask($input, $output, $question);

        if ('yes' == $answer) {
            $progressBar = new ProgressBar($output, count($indices));

            $progressBar->start();

            foreach ($indices as $index) {
                $callRequest = new CallRequestModel();
                $callRequest->setMethod('GET');
                $callRequest->setPath('/'.$index);
                $callResponse = $this->callManager->call($callRequest);

                if (Response::HTTP_OK == $callResponse->getCode()) {
                    $getIndex = $callResponse->getContent();

                    $callRequest = new CallRequestModel();
                    $callRequest->setMethod('DELETE');
                    $callRequest->setPath('/'.array_key_first($getIndex));
                    $callResponse = $this->callManager->call($callRequest);

                    $output->writeln('');
                    $output->writeln($index);
                    $output->writeln(json_encode($callResponse->getContent()));
                }
                $progressBar->advance();
            }

            $progressBar->finish();
        }

        return Command::SUCCESS;
    }
}
