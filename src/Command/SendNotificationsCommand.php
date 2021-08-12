<?php

namespace App\Command;

use App\Manager\AppNotificationManager;
use App\Manager\AppSubscriptionManager;
use App\Model\AppSubscriptionModel;
use Minishlink\WebPush\WebPush;
use Minishlink\WebPush\Subscription;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Component\HttpClient\Exception\TransportException;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;

class SendNotificationsCommand extends Command
{
    protected static $defaultName = 'app:send-notifications';

    private AppSubscriptionManager $appSubscriptionManager;

    private AppNotificationManager $appNotificationManager;

    private string $vapidPublicKey;

    private string $vapidPrivateKey;

    private HttpClientInterface $client;

    private MailerInterface $mailer;

    public function __construct(AppSubscriptionManager $appSubscriptionManager, AppNotificationManager $appNotificationManager, string $vapidPublicKey, string $vapidPrivateKey, HttpClientInterface $client, MailerInterface $mailer)
    {
        $this->appSubscriptionManager = $appSubscriptionManager;
        $this->appNotificationManager = $appNotificationManager;
        $this->vapidPublicKey = $vapidPublicKey;
        $this->vapidPrivateKey = $vapidPrivateKey;
        $this->client = $client;
        $this->mailer = $mailer;

        parent::__construct();
    }

    protected function configure(): void
    {
        $this->setDescription('Send notifications');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $notifications = $this->appNotificationManager->generate();

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

                foreach ($notifications as $notification) {
                    foreach ($subscriptions as $subscription) {
                        if (true === in_array($notification->getType(), $subscription->getNotifications())) {
                            switch ($subscription->getType()) {
                                case AppSubscriptionModel::TYPE_PUSH:
                                    $publicKey = $subscription->getPublicKey();
                                    $authenticationSecret = $subscription->getAuthenticationSecret();
                                    $contentEncoding = $subscription->getContentEncoding();

                                    if ($publicKey && $authenticationSecret && $contentEncoding) {
                                        $payload = [
                                            'tag' => uniqid('', true),
                                            'title' => $notification->getSubject(),
                                            'body' => $notification->getContent(),
                                        ];

                                        $subscription = Subscription::create([
                                            'endpoint' => $subscription->getEndpoint(),
                                            'publicKey' => $publicKey,
                                            'authToken' => $authenticationSecret,
                                            'contentEncoding' => $contentEncoding,
                                        ]);

                                        $webPush->queueNotification($subscription, json_encode($payload));
                                    }
                                    break;

                                case AppSubscriptionModel::TYPE_EMAIL:
                                    $email = (new Email())
                                        ->to($subscription->getEndpoint())
                                        ->subject($notification->getSubject())
                                        ->text($notification->getContent());

                                    $this->mailer->send($email);
                                    break;

                                case AppSubscriptionModel::TYPE_SLACK:
                                    try {
                                        $options = [
                                            'json' => [
                                                'text' => $notification->getSubject()."\r\n".$notification->getContent(),
                                            ],
                                        ];
                                        $this->client->request('POST', $subscription->getEndpoint(), $options);
                                    } catch (TransportException $e) {
                                        $output->writeln('<error>Message failed to sent for subscription '.$subscription->getEndpoint().': '.$e->getMessage().'</error>');
                                    }
                                    break;

                                case AppSubscriptionModel::TYPE_TEAMS:
                                    try {
                                        $options = [
                                            'json' => [
                                                '@context' => 'https://schema.org/extensions',
                                                '@type' => 'MessageCard',
                                                'title' => $notification->getSubject(),
                                                'text' => $notification->getContent(),
                                            ],
                                        ];
                                        $this->client->request('POST', $subscription->getEndpoint(), $options);
                                    } catch (TransportException $e) {
                                        $output->writeln('<error>Message failed to sent for subscription '.$subscription->getEndpoint().': '.$e->getMessage().'</error>');
                                    }
                                    break;
                            }
                        }
                    }

                    $this->appNotificationManager->send($notification);
                }

                foreach ($webPush->flush() as $report) {
                    $endpoint = $report->getRequest()->getUri()->__toString();

                    if ($report->isSuccess()) {
                        $output->writeln('<info>Message sent successfully for subscription '.$endpoint.'.</info>');
                    } else {
                        $output->writeln('<error>Message failed to sent for subscription '.$endpoint.': '.$report->getReason().'</error>');

                        if (true === $report->isSubscriptionExpired()) {
                            $this->appSubscriptionManager->deleteByEndpoint($endpoint);
                        }
                    }
                }
            }
        }

        return Command::SUCCESS;
    }
}
