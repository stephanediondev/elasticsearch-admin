<?php

namespace App\Command;

use App\Manager\AppNotificationManager;
use App\Manager\AppSubscriptionManager;
use App\Model\CallRequestModel;
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
        $this->setDescription('Notify');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $notifications = $this->appNotificationManager->getAll();

        if (0 < count($notifications)) {
            $subscriptions = $this->appSubscriptionManager->getAll();

            if (0 < count($subscriptions)) {
                $apiKeys = [
                    'VAPID' => [
                        'subject' => 'mailto:example@example.com',
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
                                'title' => $notification['title'],
                                'body' => $notification['body'],
                                'icon' => $notification['icon'],
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
                        $output->writeln('[v] Message sent successfully for subscription '.$endpoint.'.');
                    } else {
                        $output->writeln('[x] Message failed to sent for subscription '.$endpoint.': '.$report->getReason());
                    }
                }
            }
        }

        return Command::SUCCESS;
    }
}
