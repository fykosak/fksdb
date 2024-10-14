<?php

declare(strict_types=1);

namespace FKSDB\Modules\CoreModule;

use FKSDB\Models\Exceptions\NotImplementedException;
use FKSDB\Models\WebService\Models;
use FKSDB\Modules\Core\AuthMethod;
use Fykosak\Utils\UI\PageTitle;
use Nette\Application\AbortException;
use Nette\Application\BadRequestException;
use Nette\Application\Routers\RouteList;
use Tracy\Debugger;

final class RestApiPresenter extends \FKSDB\Modules\Core\BasePresenter
{
    public const RESOURCE_ID = 'api';

    private const ROUTER = ['module' => 'Core', 'presenter' => 'RestApi', 'action' => 'default'];

    /**
     * @persistent
     */
    public string $model;

    public function authorizedDefault(): bool
    {
        return $this->contestAuthorizator->isAllowedAnyContest(RestApiPresenter::RESOURCE_ID, $this->model);
    }

    /**
     * @throws NotImplementedException
     */
    public function titleDefault(): PageTitle
    {
        throw new NotImplementedException();
    }

    /**
     * @throws BadRequestException
     * @throws \ReflectionException
     * @throws \Throwable
     */
    public function actionDefault(): void
    {
        $params = [];
        if ($this->getHttpRequest()->getHeader('content-type') === 'application/json') {
            $params = (array)json_decode($this->getHttpRequest()->getRawBody(), true);
        }
        try {
            if (!class_exists($this->model)) {
                throw new \InvalidArgumentException();
            }
            $reflection = new \ReflectionClass($this->model);
            if (!$reflection->isSubclassOf(Models\WebModel::class)) {
                throw new \InvalidArgumentException();
            }
            /** @phpstan-var Models\WebModel<array<string,mixed>,array<string,mixed>> $model */
            $model = $reflection->newInstance($this->getContext(), array_merge($params, $this->getParameters()));

            $response = $model->getApiResponse();
            $this->sendResponse($response);
        } catch (AbortException $exception) {
            throw $exception;
        } catch (\Throwable $exception) {
            Debugger::log($exception, 'api');
            throw $exception;
        }
    }

    public function isAuthAllowed(AuthMethod $authMethod): bool
    {
        switch ($authMethod->value) {
            case AuthMethod::LOGIN:
            case AuthMethod::HTTP:
                return true;
            case AuthMethod::TOKEN:
                return false;
        }
        return false;
    }

    protected function getHttpRealm(): ?string
    {
        return 'JSON';
    }

    public static function createRouter(RouteList $list): void
    {
        $list->addRoute(
            'events/<eventId [0-9]+>/schedule',
            array_merge(self::ROUTER, [
                'model' => Models\Events\Schedule\GroupListWebModel::class,
            ])
        );/*
           $list->addRoute(
               'events/<eventId [0-9]+>/schedule/group/<groupId [0-9]+>/item',
               array_merge(self::ROUTER, [
                   'model' => ItemListWebModel::class,
               ])
           );
           $list->addRoute(
               'events/<eventId [0-9]+>/schedule/group/<groupId [0-9]+>/item/<itemId [0-9]+>/person',
               array_merge(self::ROUTER, [
                   'model' => PersonListWebModel::class,
               ])
           );*/
        $list->addRoute(
            'events/<eventId [0-9]+>/teams',
            array_merge(self::ROUTER, [
                'model' => Models\Events\TeamsWebModel::class,
            ])
        );
        $list->addRoute(
            'events/<eventId [0-9]+>/organizers',
            array_merge(self::ROUTER, [
                'model' => Models\Events\OrganizersWebModel::class,
            ])
        );
        $list->addRoute(
            'events/<eventId [0-9]+>/participants',
            array_merge(self::ROUTER, [
                'model' => Models\Events\ParticipantsWebModel::class,
            ])
        );
        $list->addRoute(
            'events/<eventId [0-9]+>/reports',
            array_merge(self::ROUTER, [
                'model' => Models\Events\ReportsWebModel::class,
            ])
        );

        $list->addRoute(
            'events/<eventId [0-9]+>/',
            array_merge(self::ROUTER, [
                'model' => Models\Events\EventDetailWebModel::class,
            ])
        );
        $list->addRoute(
            'events/',
            array_merge(self::ROUTER, [
                'model' => Models\Events\EventListWebModel::class,
            ])
        );
        $list->addRoute(
            'contests/auth',
            array_merge(self::ROUTER, [
                'model' => Models\Contests\AuthWebModel::class,
            ])
        );
        $list->addRoute(
            'contests/<contestId [0-9]+>/organizers',
            array_merge(self::ROUTER, [
                'model' => Models\Contests\OrganizersWebModel::class,
            ])
        );
        $list->addRoute(
            'contests/<contestId [0-9]+>/years/<year [0-9]+>/stats',
            array_merge(self::ROUTER, [
                'model' => Models\Contests\StatsWebModel::class,
            ])
        );
        $list->addRoute(
            'contests/<contestId [0-9]+>/years/<year [0-9]+>/results',
            array_merge(self::ROUTER, [
                'model' => Models\Contests\ResultsWebModel::class,
            ])
        );
        $list->addRoute(
            'contests/<contestId [0-9]+>',
            array_merge(self::ROUTER, [
                'model' => Models\Contests\ContestsWebModel::class,
            ])
        );
        $list->addRoute(
            'schools/reports',
            array_merge(self::ROUTER, [
                'model' => Models\SchoolsReportsWebModel::class,
            ])
        );
        $list->addRoute(
            '<model [a-zA-Z\./]+>',
            self::ROUTER
        );
    }
}
