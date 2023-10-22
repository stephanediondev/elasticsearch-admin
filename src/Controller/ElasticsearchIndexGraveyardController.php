<?php

declare(strict_types=1);

namespace App\Controller;

use App\Controller\AbstractAppController;
use App\Model\CallRequestModel;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

#[Route('/admin')]
class ElasticsearchIndexGraveyardController extends AbstractAppController
{
    #[Route('/index-graveyard', name: 'index_graveyard', methods: ['GET'])]
    public function index(Request $request): Response
    {
        $this->denyAccessUnlessGranted('INDEX_GRAVEYARD', 'global');

        if (false === $this->callManager->hasFeature('tombstones')) {
            throw new AccessDeniedException();
        }

        $clusterSettings = $this->elasticsearchClusterManager->getClusterSettings();

        $callRequest = new CallRequestModel();
        $callRequest->setQuery(['filter_path' => '**.tombstones']);
        $callRequest->setPath('/_cluster/state');
        $callResponse = $this->callManager->call($callRequest);
        $results = $callResponse->getContent();

        if (true === isset($results['metadata']['index-graveyard']['tombstones'])) {
            $tombstones = $results['metadata']['index-graveyard']['tombstones'];
            usort($tombstones, [$this, 'sortByDeleteDate']);
        } else {
            $tombstones = [];
        }

        return $this->renderAbstract($request, 'Modules/index_graveyard/index_graveyard_index.html.twig', [
            'tombstones' => $this->paginatorManager->paginate([
                'route' => 'index_graveyard',
                'route_parameters' => [],
                'total' => count($tombstones),
                'rows' => $tombstones,
                'array_slice' => true,
                'page' => $request->query->get('page'),
                'size' => 100,
            ]),
            'tombstones_size' => $clusterSettings['cluster.indices.tombstones.size'] ?? null,
        ]);
    }

    private function sortByDeleteDate(array $a, array $b): int
    {
        return $b['delete_date_in_millis'] <=> $a['delete_date_in_millis'];
    }
}
