<?php
declare(strict_types=1);

namespace App\Controller;

use App\Controller\AbstractAppController;
use App\Manager\ElasticsearchIndexManager;
use App\Model\CallRequestModel;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use \PDO;
use \PDOException;

/**
 * @Route("/admin")
 */
class AppIndexDatabaseImportController extends AbstractAppController
{
    private ElasticsearchIndexManager $elasticsearchIndexManager;

    public function __construct(ElasticsearchIndexManager $elasticsearchIndexManager)
    {
        $this->elasticsearchIndexManager = $elasticsearchIndexManager;
    }

    /**
     * @Route("/indices/{index}/database-import/connect", name="index_database_import_connect")
     */
    public function connect(Request $request, string $index): JsonResponse
    {
        $index = $this->elasticsearchIndexManager->getByName($index);

        if (null === $index) {
            throw new NotFoundHttpException();
        }

        if (true === $index->isSystem()) {
            throw new AccessDeniedException();
        }

        $fields = $request->request->all();

        try {
            $dbh = $this->getConnection($fields);

            $sql = $fields['query'].' LIMIT 1';
            $stmt = $dbh->prepare($sql);
            $stmt->execute();

            $columns = [];
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                foreach ($row as $k => $v) {
                    $columns[] = $k;
                }
            }

            $json = [
                'exception' => false,
                'columns' => $columns,
            ];
        } catch (PDOException $e) {
            $json = [
                'exception' => true,
                'message' => $e->getMessage(),
            ];
        }

        return new JsonResponse($json, JsonResponse::HTTP_OK);
    }

    /**
     * @Route("/indices/{index}/database-import/mappings", name="index_database_import_mappings")
     */
    public function mappings(Request $request, string $index): JsonResponse
    {
        $index = $this->elasticsearchIndexManager->getByName($index);

        if (null === $index) {
            throw new NotFoundHttpException();
        }

        if (true === $index->isSystem()) {
            throw new AccessDeniedException();
        }

        $fieldsRequest = $request->request->all();

        $fields = [];
        foreach ($fieldsRequest as $k => $v) {
            $k = str_replace('_DOT_', '.', $k);
            $fields[$k] = $v;
        }

        try {
            $dbh = $this->getConnection($fields);

            $sql = $fields['query'];
            $stmt = $dbh->prepare($sql);
            $stmt->execute();

            $documents = 0;

            $body = '';

            while ($row = $stmt->fetch()) {
                $id = false;
                $line = [];

                if (true === isset($fields['_id']) && '' != $fields['_id']) {
                    $id = $row[$fields['_id']];
                }

                foreach ($index->getMappingsFlat() as $field => $mapping) {
                    if (true === isset($fields[$field]) && '' != $fields[$field]) {
                        $line[$field] = $row[$fields[$field]];
                    }
                }

                if ($id) {
                    $body .= json_encode(['index' => ['_id' => $id]])."\r\n";
                } else {
                    $body .= json_encode(['index' => (object)[]])."\r\n";
                }

                $body .= json_encode($line)."\r\n";

                $documents++;
            }

            $callRequest = new CallRequestModel();
            $callRequest->setMethod('POST');
            $callRequest->setPath($index->getName().'/_bulk');
            $callRequest->setBody($body);
            $callResponse = $this->callManager->call($callRequest);

            $errors = [];

            $content = $callResponse->getContent();
            if (true === isset($content['errors']) && true === $content['errors']) {
                foreach ($content['items'] as $item) {
                    if (true === isset($item['index']['error'])) {
                        $error = [];
                        $error['_id'] = $item['index']['_id'];
                        $error['status'] = $item['index']['status'];
                        if (true === isset($item['index']['error']['caused_by']['reason'])) {
                            $error['message'] = $item['index']['error']['caused_by']['reason'];
                        } else {
                            $error['message'] = '';
                        }
                        $errors[] = $error;
                    }
                }
            }

            $callResponse = $this->elasticsearchIndexManager->refreshByName($index->getName());

            $json = [
                'exception' => false,
                'documents' => $documents - count($errors),
                'errors' => $errors,
            ];
        } catch (PDOException $e) {
            $json = [
                'exception' => true,
                'message' => $e->getMessage(),
            ];
        }

        return new JsonResponse($json, JsonResponse::HTTP_OK);
    }

    /**
     * @Route("/indices/{index}/database-import", name="index_database_import")
     */
    public function index(Request $request, string $index): Response
    {
        $index = $this->elasticsearchIndexManager->getByName($index);

        if (null === $index) {
            throw new NotFoundHttpException();
        }

        if (true === $index->isSystem()) {
            throw new AccessDeniedException();
        }

        $allowedDrivers = [];

        $availableDrivers = PDO::getAvailableDrivers();
        if (true === in_array('mysql', $availableDrivers)) {
            $allowedDrivers[] = 'mysql';
        }
        if (true === in_array('pgsql', $availableDrivers)) {
            $allowedDrivers[] = 'pgsql';
        }

        return $this->renderAbstract($request, 'Modules/app_index_database_import/app_index_database_import_index.html.twig', [
            'index' => $index,
            'drivers' => $allowedDrivers,
        ]);
    }

    private function getConnection(array $fields): PDO
    {
        $dbh = new PDO($fields['driver'].':host='.$fields['host'].';dbname='.$fields['dbname'], $fields['user'], $fields['password']);
        $dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        return $dbh;
    }
}
