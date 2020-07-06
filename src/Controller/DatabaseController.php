<?php

namespace App\Controller;

use App\Controller\AbstractAppController;
use App\Model\CallRequestModel;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;

/**
 * @Route("/admin")
 */
class DatabaseController extends AbstractAppController
{
    /**
     * @Route("/database", name="database")
     */
    public function index(Request $request): Response
    {
        return $this->renderAbstract($request, 'Modules/database/database_index.html.twig', [
            'drivers' => \PDO::getAvailableDrivers(),
        ]);
    }

    /**
     * @Route("/database/connect", name="database_connect")
     */
    public function connect(Request $request): JsonResponse
    {
        $fields = $request->request->all();

        try {
            $conn = $this->getConnection($fields);

            $sql = 'SHOW TABLES';
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

            $sql = 'SHOW COLUMNS FROM :table';
            $stmt = $conn->prepare($sql);
            $stmt->bindValue('table', $fields['table']);
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
