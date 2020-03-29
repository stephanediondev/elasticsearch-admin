<?php

namespace App\Controller;

use App\Controller\AbstractAppController;
use App\Exception\CallException;
use App\Form\CreateRepositoryType;
use App\Model\CallModel;
use App\Model\ElasticsearchRepositoryModel;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * @Route("/admin")
 */
class RepositoriesController extends AbstractAppController
{
    /**
     * @Route("repositories", name="repositories")
     */
    public function index(Request $request): Response
    {
        $call = new CallModel();
        $call->setPath('/_cat/repositories');
        $repositories = $this->callManager->call($call);

        return $this->renderAbstract($request, 'Modules/repositories/repositories_index.html.twig', [
            'repositories' => $this->paginatorManager->paginate([
                'route' => 'repositories',
                'route_parameters' => [],
                'total' => count($repositories),
                'rows' => $repositories,
                'page' => 1,
                'size' => count($repositories),
            ]),
        ]);
    }

    /**
     * @Route("/repositories/create/{type}", name="repositories_create")
     */
    public function create(Request $request, string $type): Response
    {
        $repositoryModel = new ElasticsearchRepositoryModel();
        $repositoryModel->setType($type);
        $form = $this->createForm(CreateRepositoryType::class, $repositoryModel, ['type' => $type]);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $json = [
                    'type' => $repositoryModel->getType(),
                    'settings' => [
                        'compress' => $repositoryModel->getCompress(),
                        'chunk_size' => $repositoryModel->getChunkSize(),
                        'max_restore_bytes_per_sec' => $repositoryModel->getMaxRestoreBytesPerSec(),
                        'max_snapshot_bytes_per_sec' => $repositoryModel->getMaxSnapshotBytesPerSec(),
                        'readonly' => $repositoryModel->getReadonly(),
                    ],
                ];

                if (ElasticsearchRepositoryModel::TYPE_FS == $repositoryModel->getType()) {
                    $json['settings']['location'] = $repositoryModel->getLocation();
                }

                if (ElasticsearchRepositoryModel::TYPE_S3 == $repositoryModel->getType()) {
                    $json['settings']['bucket'] = $repositoryModel->getBucket();
                    $json['settings']['client'] = $repositoryModel->getClient();
                    $json['settings']['base_path'] = $repositoryModel->getBasePath();
                    $json['settings']['server_side_encryption'] = $repositoryModel->getServerSideEncryption();
                    $json['settings']['buffer_size'] = $repositoryModel->getBufferSize();
                    $json['settings']['canned_acl'] = $repositoryModel->getCannedAcl();
                    $json['settings']['storage_class'] = $repositoryModel->getStorageClass();
                }

                if (ElasticsearchRepositoryModel::TYPE_GCS == $repositoryModel->getType()) {
                    $json['settings']['bucket'] = $repositoryModel->getBucket();
                    $json['settings']['client'] = $repositoryModel->getClient();
                    $json['settings']['base_path'] = $repositoryModel->getBasePath();
                }

                $call = new CallModel();
                $call->setMethod('PUT');
                $call->setPath('/_snapshot/'.$repositoryModel->getName());
                $call->setJson($json);
                $this->callManager->call($call);

                $this->addFlash('success', 'repositories_create');

                return $this->redirectToRoute('repositories_read', ['repository' => $repositoryModel->getName()]);
            } catch (CallException $e) {
                $this->addFlash('danger', $e->getMessage());
            }
        }

        return $this->renderAbstract($request, 'Modules/repositories/repositories_create.html.twig', [
            'form' => $form->createView(),
            'type' => $type,
        ]);
    }

    /**
     * @Route("/repositories/{repository}", name="repositories_read")
     */
    public function read(Request $request, string $repository): Response
    {
        $call = new CallModel();
        $call->setPath('/_snapshot/'.$repository);
        $repositoryQuery = $this->callManager->call($call);
        $repositoryQuery = $repositoryQuery[key($repositoryQuery)];

        $repositoryQuery['id'] = $repository;
        $repository = $repositoryQuery;

        if ($repository) {
            return $this->renderAbstract($request, 'Modules/repositories/repositories_read.html.twig', [
                'repository' => $repository,
            ]);
        } else {
            throw new NotFoundHttpException();
        }
    }

    /**
     * @Route("/repositories/{repository}/update", name="repositories_update")
     */
    public function update(Request $request, string $repository): Response
    {
        $call = new CallModel();
        $call->setPath('/_snapshot/'.$repository);
        $repositoryQuery = $this->callManager->call($call);
        $repositoryQuery = $repositoryQuery[key($repositoryQuery)];

        $repositoryQuery['id'] = $repository;
        $repository = $repositoryQuery;

        if ($repository) {
            $repositoryModel = new ElasticsearchRepositoryModel();
            $repositoryModel->convert($repository);
            $form = $this->createForm(CreateRepositoryType::class, $repositoryModel, ['type' => $repository['type'], 'update' => true]);

            $form->handleRequest($request);

            if ($form->isSubmitted() && $form->isValid()) {
                try {
                    $json = [
                        'type' => $repositoryModel->getType(),
                        'settings' => [
                            'compress' => $repositoryModel->getCompress(),
                            'chunk_size' => $repositoryModel->getChunkSize(),
                            'max_restore_bytes_per_sec' => $repositoryModel->getMaxRestoreBytesPerSec(),
                            'max_snapshot_bytes_per_sec' => $repositoryModel->getMaxSnapshotBytesPerSec(),
                            'readonly' => $repositoryModel->getReadonly(),
                        ],
                    ];

                    if (ElasticsearchRepositoryModel::TYPE_FS == $repositoryModel->getType()) {
                        $json['settings']['location'] = $repositoryModel->getLocation();
                    }

                    if (ElasticsearchRepositoryModel::TYPE_S3 == $repositoryModel->getType()) {
                        $json['settings']['bucket'] = $repositoryModel->getBucket();
                        $json['settings']['client'] = $repositoryModel->getClient();
                        $json['settings']['base_path'] = $repositoryModel->getBasePath();
                        $json['settings']['server_side_encryption'] = $repositoryModel->getServerSideEncryption();
                        $json['settings']['buffer_size'] = $repositoryModel->getBufferSize();
                        $json['settings']['canned_acl'] = $repositoryModel->getCannedAcl();
                        $json['settings']['storage_class'] = $repositoryModel->getStorageClass();
                    }

                    if (ElasticsearchRepositoryModel::TYPE_GCS == $repositoryModel->getType()) {
                        $json['settings']['bucket'] = $repositoryModel->getBucket();
                        $json['settings']['client'] = $repositoryModel->getClient();
                        $json['settings']['base_path'] = $repositoryModel->getBasePath();
                    }

                    $call = new CallModel();
                    $call->setMethod('PUT');
                    $call->setPath('/_snapshot/'.$repositoryModel->getName());
                    $call->setJson($json);
                    $this->callManager->call($call);

                    $this->addFlash('success', 'repositories_update');

                    return $this->redirectToRoute('repositories_read', ['repository' => $repositoryModel->getName()]);
                } catch (CallException $e) {
                    $this->addFlash('danger', $e->getMessage());
                }
            }

            return $this->renderAbstract($request, 'Modules/repositories/repositories_update.html.twig', [
                'repository' => $repository,
                'form' => $form->createView(),
            ]);
        } else {
            throw new NotFoundHttpException();
        }
    }

    /**
     * @Route("/repositories/{repository}/delete", name="repositories_delete")
     */
    public function delete(Request $request, string $repository): Response
    {
        $call = new CallModel();
        $call->setMethod('DELETE');
        $call->setPath('/_snapshot/'.$repository);
        $this->callManager->call($call);

        $this->addFlash('success', 'repositories_delete');

        return $this->redirectToRoute('repositories', []);
    }

    /**
     * @Route("/repositories/{repository}/cleanup", name="repositories_cleanup")
     */
    public function cleanup(Request $request, string $repository): Response
    {
        $call = new CallModel();
        $call->setMethod('POST');
        $call->setPath('/_snapshot/'.$repository.'/_cleanup');
        $results = $this->callManager->call($call);

        $this->addFlash('success', 'repositories_cleanup');

        if (true == isset($results['results'])) {
            if (true == isset($results['results']['deleted_bytes'])) {
                $this->addFlash('info', 'deleted_bytes: '.$results['results']['deleted_bytes']);
            }

            if (true == isset($results['results']['deleted_blobs'])) {
                $this->addFlash('info', 'deleted_blobs: '.$results['results']['deleted_blobs']);
            }
        }

        return $this->redirectToRoute('repositories_read', ['repository' => $repository]);
    }

    /**
     * @Route("/repositories/{repository}/verify", name="repositories_verify")
     */
    public function verify(Request $request, string $repository): Response
    {
        try {
            $call = new CallModel();
            $call->setMethod('POST');
            $call->setPath('/_snapshot/'.$repository.'/_verify');
            $results = $this->callManager->call($call);

            $this->addFlash('success', 'repositories_verify');
        } catch (CallException $e) {
            $this->addFlash('danger', $e->getMessage());
        }

        return $this->redirectToRoute('repositories_read', ['repository' => $repository]);
    }
}
