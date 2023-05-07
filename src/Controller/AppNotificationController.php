<?php
declare(strict_types=1);

namespace App\Controller;

use App\Controller\AbstractAppController;
use App\Manager\AppNotificationManager;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/admin')]
class AppNotificationController extends AbstractAppController
{
    private AppNotificationManager $appNotificationManager;

    public function __construct(AppNotificationManager $appNotificationManager)
    {
        $this->appNotificationManager = $appNotificationManager;
    }

    #[Route('/app-notifications', name: 'app_notifications')]
    public function index(Request $request): Response
    {
        $this->denyAccessUnlessGranted('APP_NOTIFICATIONS', 'global');

        $notifications = $this->appNotificationManager->getAll();

        return $this->renderAbstract($request, 'Modules/app_notification/app_notification_index.html.twig', [
            'notifications' => $this->paginatorManager->paginate([
                'route' => 'app_notifications',
                'route_parameters' => [],
                'total' => count($notifications),
                'rows' => $notifications,
                'array_slice' => true,
                'page' => $request->query->get('page'),
                'size' => 100,
            ]),
        ]);
    }
}
