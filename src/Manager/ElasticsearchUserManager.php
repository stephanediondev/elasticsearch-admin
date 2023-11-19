<?php

declare(strict_types=1);

namespace App\Manager;

use App\Manager\AbstractAppManager;
use App\Model\CallRequestModel;
use App\Model\CallResponseModel;
use App\Model\ElasticsearchUserModel;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Contracts\Service\Attribute\Required;

class ElasticsearchUserManager extends AbstractAppManager
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

    public function getByName(string $name): ?ElasticsearchUserModel
    {
        if (false === $this->callManager->hasFeature('security')) {
            $userModel = null;
        } else {
            $callRequest = new CallRequestModel();
            $callRequest->setPath($this->getEndpoint().'/user/'.$name);
            $callResponse = $this->callManager->call($callRequest);

            if (Response::HTTP_NOT_FOUND == $callResponse->getCode()) {
                $userModel = null;
            } else {
                $user = $callResponse->getContent();
                $userNice = $user[key($user)];
                $userNice['name'] = key($user);

                $userModel = new ElasticsearchUserModel();
                $userModel->convert($userNice);
            }
        }

        return $userModel;
    }

    /**
     * @param array<mixed> $filter
     * @return array<mixed>
     */
    public function getAll(array $filter = []): array
    {
        $callRequest = new CallRequestModel();
        $callRequest->setPath($this->getEndpoint().'/user');
        $callResponse = $this->callManager->call($callRequest);
        $results = $callResponse->getContent();

        $users = [];
        foreach ($results as $k => $row) {
            $row['name'] = $k;
            $userModel = new ElasticsearchUserModel();
            $userModel->convert($row);
            $users[$k] = $userModel;
        }
        ksort($users);

        return $this->filter($users, $filter);
    }

    /**
     * @param array<mixed> $users
     * @param array<mixed> $filter
     * @return array<mixed>
     */
    public function filter(array $users, array $filter = []): array
    {
        $usersWithFilter = [];

        foreach ($users as $row) {
            $score = 0;

            if (true === isset($filter['enabled'])) {
                if ('yes' === $filter['enabled'] && false === $row->getEnabled()) {
                    $score--;
                }
                if ('no' === $filter['enabled'] && true === $row->getEnabled()) {
                    $score--;
                }
            }

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
                $usersWithFilter[] = $row;
            }
        }

        return $usersWithFilter;
    }

    public function deleteByName(string $name): CallResponseModel
    {
        $callRequest = new CallRequestModel();
        $callRequest->setMethod('DELETE');
        $callRequest->setPath($this->getEndpoint().'/user/'.$name);

        return $this->callManager->call($callRequest);
    }

    public function enableByName(string $name): CallResponseModel
    {
        $callRequest = new CallRequestModel();
        $callRequest->setMethod('PUT');
        $callRequest->setPath($this->getEndpoint().'/user/'.$name.'/_enable');

        return $this->callManager->call($callRequest);
    }

    public function disableByName(string $name): CallResponseModel
    {
        $callRequest = new CallRequestModel();
        $callRequest->setMethod('PUT');
        $callRequest->setPath($this->getEndpoint().'/user/'.$name.'/_disable');

        return $this->callManager->call($callRequest);
    }

    /**
     * @return array<mixed>
     */
    public function selectUsers(): array
    {
        $users = [];

        $callRequest = new CallRequestModel();
        $callRequest->setPath($this->getEndpoint().'/user');
        $callResponse = $this->callManager->call($callRequest);
        $rows = $callResponse->getContent();

        foreach ($rows as $k => $row) {
            $users[] = $k;
        }

        sort($users);

        return $users;
    }
}
