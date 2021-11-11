<?php

namespace App\Controller;

use App\Controller\AbstractAppController;
use App\Exception\CallException;
use App\Form\Type\AppSubscriptionType;
use App\Model\CallRequestModel;
use App\Manager\AppSubscriptionManager;
use App\Manager\AppNotificationManager;
use App\Manager\AppUserManager;
use App\Model\AppNotificationModel;
use App\Model\AppUserModel;
use App\Model\AppSubscriptionModel;
use DeviceDetector\DeviceDetector;
use Minishlink\WebPush\WebPush;
use Minishlink\WebPush\Subscription;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

/**
 * @Route("/admin")
 */
class AppSubscriptionsController extends AbstractAppController
{
    private AppSubscriptionManager $appSubscriptionManager;

    private AppNotificationManager $appNotificationManager;

    private AppUserManager $appUserManager;

    private string $vapidPublicKey;

    private string $vapidPrivateKey;

    private string $mailerDsn;

    private string $senderAddress;

    private $clusterHealth;

    public function __construct(AppSubscriptionManager $appSubscriptionManager, AppNotificationManager $appNotificationManager, AppUserManager $appUserManager, string $vapidPublicKey, string $vapidPrivateKey, string $mailerDsn, string $senderAddress)
    {
        $this->appSubscriptionManager = $appSubscriptionManager;
        $this->appNotificationManager = $appNotificationManager;
        $this->appUserManager = $appUserManager;
        $this->vapidPublicKey = $vapidPublicKey;
        $this->vapidPrivateKey = $vapidPrivateKey;
        $this->mailerDsn = $mailerDsn;
        $this->senderAddress = $senderAddress;
    }

    /**
     * @Route("/subscriptions", name="app_subscriptions")
     */
    public function index(Request $request): Response
    {
        $this->denyAccessUnlessGranted('APP_SUBSCRIPTIONS', 'global');

        if (false === $this->appNotificationManager->infoFileExists()) {
            $this->addFlash('warning', 'Add to cron */5 * * * * cd '.str_replace('/public/index.php', '', $request->server->get('SCRIPT_FILENAME')).' && bin/console app:send-notifications');
        }

        $user = $this->appUserManager->getByEmail($this->getuser()->getUserIdentifier());

        $query = [
            'q' => 'user_id:"'.$user->getId().'"',
        ];
        $subscriptions = $this->appSubscriptionManager->getAll($query);

        return $this->renderAbstract($request, 'Modules/subscription/subscription_index.html.twig', [
            'subscriptions' => $subscriptions,
            'applicationServerKey' => $this->vapidPublicKey,
            'mailerDsn' => $this->mailerDsn,
        ]);
    }

    /**
     * @Route("/subscriptions/create/{type}", name="app_subscriptions_create")
     */
    public function create(Request $request, string $type): Response
    {
        $this->denyAccessUnlessGranted('APP_SUBSCRIPTIONS', 'global');

        if (false === in_array($type, AppSubscriptionModel::getTypes())) {
            throw new AccessDeniedException();
        }

        if (AppSubscriptionModel::TYPE_EMAIL == $type) {
            if ('null://null' == $this->mailerDsn || '' == $this->senderAddress) {
                $this->addFlash('warning', 'Edit MAILER_DSN and SENDER_ADDRESS in .env file');

                throw new AccessDeniedException();
            }
        }

        if (AppSubscriptionModel::TYPE_PUSH == $type) {
            if (false === $request->isSecure()) {
                $this->addFlash('warning', 'Push API available only with HTTPS');

                throw new AccessDeniedException();
            }

            if ('' == $this->vapidPublicKey || '' == $this->vapidPrivateKey) {
                $this->addFlash('warning', 'Run bin/console app:generate-vapid');
                $this->addFlash('warning', 'Edit VAPID_PUBLIC_KEY and VAPID_PRIVATE_KEY in .env file');

                throw new AccessDeniedException();
            }
        }

        $dd = new DeviceDetector($request->headers->get('User-Agent'));
        $dd->skipBotDetection();
        $dd->parse();

        $client = $dd->getClient();
        $os = $dd->getOs();

        $user = $this->appUserManager->getByEmail($this->getuser()->getUserIdentifier());

        $subscription = new AppSubscriptionModel();
        $subscription->setUserId($user->getId());
        $subscription->setType($type);
        $subscription->setIp($request->getClientIp());
        $subscription->setOs($os ? $os['name'].' '.$os['version'] : false);
        $subscription->setClient($client ? $client['name'].' '.$client['version'] : false);
        $subscription->setNotifications(AppNotificationModel::getTypes());

        $form = $this->createForm(AppSubscriptionType::class, $subscription, ['type' => $type]);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $callResponse = $this->appSubscriptionManager->send($subscription);

                $this->addFlash('info', json_encode($callResponse->getContent()));

                return $this->redirectToRoute('app_subscriptions');
            } catch (CallException $e) {
                $this->addFlash('danger', $e->getMessage());
            }
        }

        return $this->renderAbstract($request, 'Modules/subscription/subscription_create.html.twig', [
            'form' => $form->createView(),
            'type' => $type,
            'applicationServerKey' => $this->vapidPublicKey,
            'mailerDsn' => $this->mailerDsn,
        ]);
    }

    /**
     * @Route("/subscriptions/{id}/update", name="app_subscriptions_update")
     */
    public function update(Request $request, string $id): Response
    {
        $this->denyAccessUnlessGranted('APP_SUBSCRIPTIONS', 'global');

        $subscription = $this->appSubscriptionManager->getById($id);

        if (null === $subscription) {
            throw new NotFoundHttpException();
        }

        $form = $this->createForm(AppSubscriptionType::class, $subscription, ['type' => $subscription->getType(), 'context' => 'update']);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $callResponse = $this->appSubscriptionManager->send($subscription);

                $this->addFlash('info', json_encode($callResponse->getContent()));

                return $this->redirectToRoute('app_subscriptions');
            } catch (CallException $e) {
                $this->addFlash('danger', $e->getMessage());
            }
        }

        return $this->renderAbstract($request, 'Modules/subscription/subscription_update.html.twig', [
            'subscription' => $subscription,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/subscriptions/{id}/delete", name="app_subscriptions_delete")
     */
    public function delete(Request $request, string $id): Response
    {
        $this->denyAccessUnlessGranted('APP_SUBSCRIPTIONS', 'global');

        $subscription = $this->appSubscriptionManager->getById($id);

        if (null === $subscription) {
            throw new NotFoundHttpException();
        }

        $callResponse = $this->appSubscriptionManager->deleteById($subscription->getId());

        $this->addFlash('info', json_encode($callResponse->getContent()));

        return $this->redirectToRoute('app_subscriptions');
    }

    /**
     * @Route("/subscriptions/{id}/test", name="app_subscriptions_test")
     */
    public function test(Request $request, string $id): JsonResponse
    {
        $this->denyAccessUnlessGranted('APP_SUBSCRIPTIONS', 'global');

        $subscription = $this->appSubscriptionManager->getById($id);

        if (null === $subscription) {
            throw new NotFoundHttpException();
        }

        $this->clusterHealth = $this->elasticsearchClusterManager->getClusterHealth();

        $json = [];

        $notification = new AppNotificationModel();
        $notification->setType(AppNotificationModel::TYPE_CLUSTER_HEALTH);
        $notification->setCluster($this->clusterHealth['cluster_name']);
        $notification->setTitle('health');
        $notification->setContent(ucfirst($this->clusterHealth['status']));
        $notification->setColor($this->clusterHealth['status']);

        switch ($subscription->getType()) {
            case AppSubscriptionModel::TYPE_PUSH:
                $apiKeys = [
                    'VAPID' => [
                        'subject' => 'https://github.com/stephanediondev/elasticsearch-admin',
                        'publicKey' => $this->vapidPublicKey,
                        'privateKey' => $this->vapidPrivateKey,
                    ],
                ];

                $webPush = new WebPush($apiKeys);

                $publicKey = $subscription->getPublicKey();
                $authenticationSecret = $subscription->getAuthenticationSecret();
                $contentEncoding = $subscription->getContentEncoding();

                if ($publicKey && $authenticationSecret && $contentEncoding) {
                    $payload = [
                        'tag' => uniqid('', true),
                        'title' => $notification->getSubject(),
                        'body' => $notification->getContent(),
                    ];

                    $subcription = Subscription::create([
                        'endpoint' => $subscription->getEndpoint(),
                        'publicKey' => $publicKey,
                        'authToken' => $authenticationSecret,
                        'contentEncoding' => $contentEncoding,
                    ]);

                    $report = $webPush->sendOneNotification($subcription, json_encode($payload));

                    if ($report->isSuccess()) {
                        $json['message'] = 'Message sent successfully for subscription '.$subscription->getEndpoint().'.';
                    } else {
                        $json['message'] = 'Message failed to sent for subscription '.$subscription->getEndpoint().': '.$report->getReason().'.';
                    }
                }
            break;
        }

        return new JsonResponse($json, JsonResponse::HTTP_OK);
    }
}
