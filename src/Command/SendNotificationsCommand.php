<?php

namespace App\Command;

use App\Manager\AppNotificationManager;
use App\Manager\AppSubscriptionManager;
use Minishlink\WebPush\WebPush;
use Minishlink\WebPush\Subscription;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class SendNotificationsCommand extends Command
{
    protected static $defaultName = 'app:send-notifications';

    public function __construct(AppSubscriptionManager $appSubscriptionManager, AppNotificationManager $appNotificationManager, string $vapidPublicKey, string $vapidPrivateKey)
    {
        $this->appSubscriptionManager = $appSubscriptionManager;
        $this->appNotificationManager = $appNotificationManager;
        $this->vapidPublicKey = $vapidPublicKey;
        $this->vapidPrivateKey = $vapidPrivateKey;

        parent::__construct();
    }

    protected function configure()
    {
        $this->setDescription('Send notifications');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $notifications = $this->appNotificationManager->getAll();

        if (0 < count($notifications)) {
            $subscriptions = $this->appSubscriptionManager->getAll();

            if (0 < count($subscriptions)) {
                $apiKeys = [
                    'VAPID' => [
                        'subject' => 'https://github.com/stephanediondev/elasticsearch-admin',
                        'publicKey' => $this->vapidPublicKey,
                        'privateKey' => $this->vapidPrivateKey,
                    ],
                ];

                $webPush = new WebPush($apiKeys);

                foreach ($subscriptions as $subscription) {
                    $publicKey = $subscription->getPublicKey();
                    $authenticationSecret = $subscription->getAuthenticationSecret();
                    $contentEncoding = $subscription->getContentEncoding();

                    if ($publicKey && $authenticationSecret && $contentEncoding) {
                        foreach ($notifications as $notification) {
                            $payload = [
                                'tag' => uniqid('', true),
                                'title' => $notification->getTitle(),
                                'body' => $notification->getBody(),
                                'icon' => $notification->getIcon(),
                            ];

                            $subcription = Subscription::create([
                                'endpoint' => $subscription->getEndpoint(),
                                'publicKey' => $publicKey,
                                'authToken' => $authenticationSecret,
                                'contentEncoding' => $contentEncoding,
                            ]);

                            $webPush->sendNotification($subcription, json_encode($payload), false);
                        }
                    }
                }

                foreach ($webPush->flush() as $report) {
                    $endpoint = $report->getRequest()->getUri()->__toString();

                    if ($report->isSuccess()) {
                        $output->writeln('<info>Message sent successfully for subscription '.$endpoint.'.</info>');
                    } else {
                        $output->writeln('<error>Message failed to sent for subscription '.$endpoint.': '.$report->getReason().'</error>');
                    }
                }
            }
        }

        return Command::SUCCESS;
    }
}
