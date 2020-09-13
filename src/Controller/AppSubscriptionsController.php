<?php

namespace App\Controller;

use App\Controller\AbstractAppController;
use App\Model\CallRequestModel;
use App\Manager\AppSubscriptionManager;
use App\Manager\AppNotificationManager;
use App\Model\AppSubscriptionModel;
use DeviceDetector\DeviceDetector;
use Minishlink\WebPush\WebPush;
use Minishlink\WebPush\Subscription;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * @Route("/admin")
 */
class AppSubscriptionsController extends AbstractAppController
{
    public function __construct(AppSubscriptionManager $appSubscriptionManager, AppNotificationManager $appNotificationManager, Security $security)
    {
        $this->appSubscriptionManager = $appSubscriptionManager;
        $this->appNotificationManager = $appNotificationManager;
        $this->user = $security->getUser();
    }

    /**
     * @Route("/subscriptions", name="app_subscriptions")
     */
    public function index(Request $request, string $vapidPublicKey, string $vapidPrivateKey): Response
    {
        $this->denyAccessUnlessGranted('APP_SUBSCRIPTIONS', 'global');

        if ('' == $vapidPublicKey || '' == $vapidPrivateKey) {
            $this->addFlash('warning', 'Run bin/console app:generate-vapid');
            $this->addFlash('warning', 'Edit VAPID_PUBLIC_KEY and VAPID_PRIVATE_KEY in .env file');
        }

        if (false === $this->appNotificationManager->infoFileExists()) {
            $this->addFlash('warning', 'Add to cron */5 * * * * cd '.str_replace('/public', '', $request->getBasePath()).' && bin/console app:send-notifications');
        }

        $query = [
            'q' => 'user_id:"'.$this->user->getId().'"',
        ];
        $subscriptions = $this->appSubscriptionManager->getAll($query);

        return $this->renderAbstract($request, 'Modules/subscription/subscription_index.html.twig', [
            'subscriptions' => $subscriptions,
            'applicationServerKey' => $vapidPublicKey,
        ]);
    }

    /**
     * @Route("/subscriptions/create", name="app_subscriptions_create")
     */
    public function create(Request $request): JsonResponse
    {
        $this->denyAccessUnlessGranted('APP_SUBSCRIPTIONS', 'global');

        $json = [];

        if ($content = $request->getContent()) {
            $content = json_decode($content, true);

            $dd = new DeviceDetector($request->headers->get('User-Agent'));
            $dd->skipBotDetection();
            $dd->parse();

            $client = $dd->getClient();
            $os = $dd->getOs();

            $subscription = new AppSubscriptionModel();
            $subscription->setUserId($this->user->getId());
            $subscription->setType($content['type']);
            $subscription->setEndpoint($content['endpoint']);
            if (AppSubscriptionModel::TYPE_PUSH == $content['type']) {
                $subscription->setPublicKey($content['public_key']);
                $subscription->setAuthenticationSecret($content['authentication_secret']);
                $subscription->setContentEncoding($content['content_encoding']);
            }
            $subscription->setIp($request->getClientIp());
            $subscription->setOs($os ? $os['name'].' '.$os['version'] : false);
            $subscription->setClient($client ? $client['name'].' '.$client['version'] : false);

            $callResponse = $this->appSubscriptionManager->send($subscription);

            return new JsonResponse(json_encode($callResponse->getContent()), JsonResponse::HTTP_OK);
        }

        return new JsonResponse($json, JsonResponse::HTTP_OK);
    }

    /**
     * @Route("/subscriptions/delete/{id}", name="app_subscriptions_delete")
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
     * @Route("/subscriptions/test", name="app_subscriptions_test")
     */
    public function test(Request $request, string $vapidPublicKey, string $vapidPrivateKey): JsonResponse
    {
        $this->denyAccessUnlessGranted('APP_SUBSCRIPTIONS', 'global');

        $json = [];

        $query = [
            'q' => 'user_id:"'.$this->user->getId().'"',
        ];
        $subscriptions = $this->appSubscriptionManager->getAll($query);

        $apiKeys = [
            'VAPID' => [
                'subject' => 'https://github.com/stephanediondev/elasticsearch-admin',
                'publicKey' => $vapidPublicKey,
                'privateKey' => $vapidPrivateKey,
            ],
        ];

        $webPush = new WebPush($apiKeys);

        foreach ($subscriptions as $subscription) {
            $publicKey = $subscription->getPublicKey();
            $authenticationSecret = $subscription->getAuthenticationSecret();
            $contentEncoding = $subscription->getContentEncoding();

            if ($publicKey && $authenticationSecret && $contentEncoding) {
                $payload = [
                    'tag' => uniqid('', true),
                    'title' => 'test',
                    'body' => 'test',
                    'icon' => 'favicon-green-144.png',
                ];

                $subcription = Subscription::create([
                    'endpoint' => $subscription->getEndpoint(),
                    'publicKey' => $publicKey,
                    'authToken' => $authenticationSecret,
                    'contentEncoding' => $contentEncoding,
                ]);

                $webPush->queueNotification($subcription, json_encode($payload));
            }
        }

        foreach ($webPush->flush() as $report) {
        }

        return new JsonResponse($json, JsonResponse::HTTP_OK);
    }
}
