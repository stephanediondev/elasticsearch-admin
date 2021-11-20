<?php
declare(strict_types=1);

namespace App\Controller;

use App\Controller\AbstractAppController;
use App\Form\Type\AppThemeType;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Cookie;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\RedirectResponse;

/**
 * @Route("/admin")
 */
class AppThemeController extends AbstractAppController
{
    /**
     * @Route("/theme-editor", name="app_theme_editor")
     */
    public function index(Request $request): Response
    {
        $theme = [];

        $saved = $request->cookies->get('theme') ? json_decode($request->cookies->get('theme'), true) : [];
        $predefined = $this->appThemeManager->getPredefined($this->themeDefault);
        foreach ($predefined as $k => $v) {
            $theme[$k] = $saved[$k] ?? $v;
        }

        $form = $this->createForm(AppThemeType::class, $theme, [
        ]);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $cookie = Cookie::create('theme', json_encode($form->getData()), 2147483647, '/', null, true, false, false, Cookie::SAMESITE_LAX);
            $response = new RedirectResponse($request->getUri());
            $response->headers->setCookie($cookie);

            $this->addFlash('info', 'theme_saved');

            return $response;
        }

        return $this->renderAbstract($request, 'Modules/app_theme/app_theme_editor.html.twig', [
            'form' => $form->createView(),
            'predefined' => $this->appThemeManager->predefinedList(),
        ]);
    }
}
