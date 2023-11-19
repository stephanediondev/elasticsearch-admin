<?php

declare(strict_types=1);

namespace App\Manager;

use App\Manager\AbstractAppManager;
use App\Model\CallRequestModel;
use App\Model\CallResponseModel;
use App\Model\ElasticsearchRoleModel;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Contracts\Service\Attribute\Required;

class ElasticsearchRoleManager extends AbstractAppManager
{
    protected string $endpoint;

    #[Required]
    public function setEndpoint(): void
    {
        if (true === $this->callManager->hasFeature('_security_endpoint')) {
            $this->endpoint = '/_security';
        } else {
            $this->endpoint = '/_xpack/security';
        }
    }

    public function getEndpoint(): string
    {
        return $this->endpoint;
    }

    public function getByName(string $name): ?ElasticsearchRoleModel
    {
        if (false === $this->callManager->hasFeature('security')) {
            $roleModel = null;
        } else {
            $callRequest = new CallRequestModel();
            $callRequest->setPath($this->getEndpoint().'/role/'.$name);
            $callResponse = $this->callManager->call($callRequest);

            if (Response::HTTP_NOT_FOUND == $callResponse->getCode()) {
                $roleModel = null;
            } else {
                $role = $callResponse->getContent();
                $roleNice = $role[key($role)];
                $roleNice['name'] = key($role);

                $roleModel = new ElasticsearchRoleModel();
                $roleModel->convert($roleNice);
            }
        }

        return $roleModel;
    }

    /**
     * @param array<mixed> $filter
     * @return array<mixed>
     */
    public function getAll(array $filter = []): array
    {
        $callRequest = new CallRequestModel();
        $callRequest->setPath($this->getEndpoint().'/role');
        $callResponse = $this->callManager->call($callRequest);
        $results = $callResponse->getContent();

        $roles = [];
        if ($results) {
            foreach ($results as $k => $row) {
                $row['name'] = $k;
                $roleModel = new ElasticsearchRoleModel();
                $roleModel->convert($row);
                $roles[$k] = $roleModel;
            }
            ksort($roles);
        }

        return $this->filter($roles, $filter);
    }

    /**
     * @param array<mixed> $roles
     * @param array<mixed> $filter
     * @return array<mixed>
     */
    public function filter(array $roles, array $filter = []): array
    {
        $rolesWithFilter = [];

        foreach ($roles as $row) {
            $score = 0;

            if (true === isset($filter['reserved'])) {
                if ('yes' === $filter['reserved'] && false === $row->isReserved()) {
                    $score--;
                }
                if ('no' === $filter['reserved'] && true === $row->isReserved()) {
                    $score--;
                }
            }

            if (true === isset($filter['deprecated'])) {
                if ('yes' === $filter['deprecated'] && false === $row->isDeprecated()) {
                    $score--;
                }
                if ('no' === $filter['deprecated'] && true === $row->isDeprecated()) {
                    $score--;
                }
            }

            if (0 <= $score) {
                $rolesWithFilter[] = $row;
            }
        }

        return $rolesWithFilter;
    }

    public function send(ElasticsearchRoleModel $roleModel): CallResponseModel
    {
        $json = $roleModel->getJson();
        $callRequest = new CallRequestModel();
        $callRequest->setMethod('PUT');
        $callRequest->setPath($this->getEndpoint().'/role/'.$roleModel->getName());
        $callRequest->setJson($json);

        return $this->callManager->call($callRequest);
    }

    public function deleteByName(string $name): CallResponseModel
    {
        $callRequest = new CallRequestModel();
        $callRequest->setMethod('DELETE');
        $callRequest->setPath($this->getEndpoint().'/role/'.$name);

        return $this->callManager->call($callRequest);
    }

    /**
     * @return array<mixed>
     */
    public function selectRoles(): array
    {
        $roles = [];

        $callRequest = new CallRequestModel();
        $callRequest->setPath($this->getEndpoint().'/role');
        $callResponse = $this->callManager->call($callRequest);
        $rows = $callResponse->getContent();

        if ($rows) {
            foreach ($rows as $k => $row) {
                $roles[] = $k;
            }
            sort($roles);
        }

        return $roles;
    }

    /**
     * @return array<mixed>
     */
    public function getPrivileges(): array
    {
        if (true === $this->callManager->hasFeature('builtin_privileges')) {
            $callRequest = new CallRequestModel();
            $callRequest->setPath($this->getEndpoint().'/privilege/_builtin');
            $callResponse = $this->callManager->call($callRequest);
            return $callResponse->getContent();
        } else {
            return [
                'cluster' => [
                    'all',
                    'create_snapshot',
                    'delegate_pki',
                    'grant_api_key',
                    'manage',
                    'manage_api_key',
                    'manage_autoscaling',
                    'manage_ccr',
                    'manage_data_frame_transforms',
                    'manage_enrich',
                    'manage_ilm',
                    'manage_index_templates',
                    'manage_ingest_pipelines',
                    'manage_ml',
                    'manage_oidc',
                    'manage_own_api_key',
                    'manage_pipeline',
                    'manage_rollup',
                    'manage_saml',
                    'manage_security',
                    'manage_slm',
                    'manage_token',
                    'manage_transform',
                    'manage_watcher',
                    'monitor',
                    'monitor_data_frame_transforms',
                    'monitor_ml',
                    'monitor_rollup',
                    'monitor_snapshot',
                    'monitor_transform',
                    'monitor_watcher',
                    'none',
                    'read_ccr',
                    'read_ilm',
                    'read_slm',
                    'transport_client',
                ],
                'index' => [
                    'all',
                    'create',
                    'create_doc',
                    'create_index',
                    'delete',
                    'delete_index',
                    'index',
                    'maintenance',
                    'manage',
                    'manage_follow_index',
                    'manage_ilm',
                    'manage_leader_index',
                    'monitor',
                    'none',
                    'read',
                    'read_cross_cluster',
                    'view_index_metadata',
                    'write',
                ],
            ];
        }
    }
}
