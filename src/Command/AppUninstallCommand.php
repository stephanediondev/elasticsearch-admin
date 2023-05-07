<?php

declare(strict_types=1);

namespace App\Command;

use App\Manager\AppManager;
use App\Manager\CallManager;
use App\Model\CallRequestModel;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Helper\QuestionHelper;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\Question;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Contracts\Translation\TranslatorInterface;

#[AsCommand(name: 'app:uninstall')]
class AppUninstallCommand extends Command
{
    private AppManager $appManager;

    private CallManager $callManager;

    private TranslatorInterface $translator;

    public function __construct(AppManager $appManager, CallManager $callManager, TranslatorInterface $translator)
    {
        $this->appManager = $appManager;
        $this->callManager = $callManager;
        $this->translator = $translator;

        parent::__construct();
    }

    protected function configure(): void
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

        $helper = new QuestionHelper();

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
                    if ($getIndex = $callResponse->getContent()) {
                        $callRequest = new CallRequestModel();
                        $callRequest->setMethod('DELETE');
                        $callRequest->setPath('/'.array_key_first($getIndex));
                        $callResponse = $this->callManager->call($callRequest);

                        $output->writeln('');
                        $output->writeln($index);
                        if ($json = json_encode($callResponse->getContent())) {
                            $output->writeln($json);
                        }
                    }
                }
                $progressBar->advance();
            }

            $progressBar->finish();
        }

        return Command::SUCCESS;
    }
}
