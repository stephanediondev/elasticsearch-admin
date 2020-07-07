<?php

namespace App\Controller;

use App\Controller\AbstractAppController;
use App\Manager\ElasticsearchIndexManager;
use App\Model\CallRequestModel;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

/**
 * @Route("/admin")
 */
class DatabaseController extends AbstractAppController
{
    public function __construct(ElasticsearchIndexManager $elasticsearchIndexManager)
    {
        $this->elasticsearchIndexManager = $elasticsearchIndexManager;
    }

        /**
     * @Route("/database/connect", name="database_connect")
     */
    public function connect(Request $request): JsonResponse
    {
        $fields = $request->request->all();

        try {
            $conn = $this->getConnection($fields);

            if ('mysql' == $fields['driver']) {
                $sql = 'SHOW TABLES';
            }

            if ('pgsql' == $fields['driver']) {
                $sql = 'SELECT tablename FROM pg_catalog.pg_tables WHERE schemaname != \'pg_catalog\' AND schemaname != \'information_schema\' ORDER BY tablename';
            }

            $stmt = $conn->query($sql);

            $tables = [];
            while ($row = $stmt->fetch()) {
                $tables[] = $row[array_key_first($row)];
            }

            $json = [
                'error' => false,
                'tables' => $tables,
            ];

        } catch(\Exception $e) {
            $json = [
                'error' => true,
                'message' => $e->getMessage(),
            ];
        }

        return new JsonResponse($json, JsonResponse::HTTP_OK);
    }

    /**
     * @Route("/database/table", name="database_table")
     */
    public function table(Request $request): JsonResponse
    {
        $fields = $request->request->all();

        try {
            $conn = $this->getConnection($fields);

            if ('mysql' == $fields['driver']) {
                $sql = 'SHOW COLUMNS FROM '.$fields['table'];
            }

            if ('pgsql' == $fields['driver']) {
                $sql = 'SELECT column_name FROM information_schema.columns WHERE table_name = \''.$fields['table'].'\' ORDER BY column_name';
            }

            $stmt = $conn->prepare($sql);
            $stmt->execute();

            $columns = [];
            while ($row = $stmt->fetch()) {
                $columns[] = $row[array_key_first($row)];
            }

            $json = [
                'error' => false,
                'columns' => $columns,
            ];

        } catch(\Exception $e) {
            $json = [
                'error' => true,
                'message' => $e->getMessage(),
            ];
        }

        return new JsonResponse($json, JsonResponse::HTTP_OK);
    }

    /**
     * @Route("/database/{index}", name="database")
     */
    public function index(Request $request, string $index): Response
    {
        $index = $this->elasticsearchIndexManager->getByName($index);

        if (false == $index) {
            throw new NotFoundHttpException();
        }

        if (true == $index->isSystem()) {
            throw new AccessDeniedHttpException();
        }

        $allowedDrivers = [];

        $availableDrivers = \PDO::getAvailableDrivers();
        if (true == in_array('mysql', $availableDrivers)) {
            $allowedDrivers[] = 'mysql';
        }
        if (true == in_array('pgsql', $availableDrivers)) {
            $allowedDrivers[] = 'pgsql';
        }

        return $this->renderAbstract($request, 'Modules/database/database_index.html.twig', [
            'index' => $index,
            'drivers' => $allowedDrivers,
        ]);
    }

    private function getConnection($fields)
    {
        $connectionParams = [
            'dbname' => $fields['dbname'],
            'user' => $fields['user'],
            'password' => $fields['password'],
            'host' => $fields['host'],
            'driver' => 'pdo_'.$fields['driver'],
        ];

        return \Doctrine\DBAL\DriverManager::getConnection($connectionParams);
    }
}
