<?php
declare(strict_types=1);

namespace App\Controller;

use App\Controller\AbstractAppController;
use App\Exception\CallException;
use App\Form\Type\ElasticsearchSqlType;
use App\Model\CallRequestModel;
use App\Model\ElasticsearchSqlModel;
use Box\Spout\Writer\Common\Creator\WriterEntityFactory;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

/**
 * @Route("/admin")
 */
class ElasticsearchSqlController extends AbstractAppController
{
    /**
     * @Route("/sql", name="sql")
     */
    public function index(Request $request): Response
    {
        $this->denyAccessUnlessGranted('SQL', 'global');

        if (false === $this->callManager->hasFeature('sql')) {
            throw new AccessDeniedException();
        }

        $parameters = [];

        $sqlModel = new ElasticsearchSqlModel();
        $form = $this->createForm(ElasticsearchSqlType::class, $sqlModel);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $json = $sqlModel->getJson();
                $callRequest = new CallRequestModel();
                $callRequest->setMethod('POST');
                $callRequest->setPath('/_sql');
                $callRequest->setJson($json);
                $callResponse = $this->callManager->call($callRequest);

                $parameters['query'] = $callResponse->getContent();

                $json = $sqlModel->getJson();
                $callRequest = new CallRequestModel();
                $callRequest->setMethod('POST');
                $callRequest->setPath('/_sql/translate');
                $callRequest->setJson($json);
                $callResponse = $this->callManager->call($callRequest);

                $parameters['translation_to_dsl'] = $callResponse->getContent();
            } catch (CallException $e) {
                $this->addFlash('danger', $e->getMessage());
            }
        }

        $parameters['form'] = $form->createView();

        return $this->renderAbstract($request, 'Modules/sql/sql_index.html.twig', $parameters);
    }

    /**
     * @Route("/sql/cursor", name="sql_cursor")
     */
    public function cursor(Request $request): JsonResponse
    {
        $this->denyAccessUnlessGranted('SQL', 'global');

        if (false === $this->callManager->hasFeature('sql')) {
            throw new AccessDeniedException();
        }

        $content = $request->getContent();
        $content = json_decode($content, true);

        try {
            $json = [
                'cursor' => $content['cursor'],
            ];
            $callRequest = new CallRequestModel();
            $callRequest->setMethod('POST');
            $callRequest->setPath('/_sql');
            $callRequest->setJson($json);
            $callResponse = $this->callManager->call($callRequest);

            $json = [
                'exception' => false,
                'content' => $callResponse->getContent(),
            ];
        } catch (CallException $e) {
            $json = [
                'exception' => true,
                'message' => $e->getMessage(),
            ];
        }

        return new JsonResponse($json, JsonResponse::HTTP_OK);
    }
}
