<?php

declare(strict_types=1);

namespace FKSDB\Modules\CoreModule;

use FKSDB\Models\WebService\Models\ContestsModel;
use FKSDB\Models\WebService\Models\Event\ParticipantsWebModel;
use FKSDB\Models\WebService\Models\Event\TeamsWebModel;
use FKSDB\Models\WebService\Models\EventListWebModel;
use FKSDB\Models\WebService\Models\EventWebModel;
use FKSDB\Models\WebService\Models\OrganizersWebModel;
use FKSDB\Models\WebService\Models\PaymentListWebModel;
use FKSDB\Models\WebService\Models\ResultsWebModel;
use FKSDB\Models\WebService\Models\SeriesResultsWebModel;
use FKSDB\Models\WebService\Models\SignaturesWebModel;
use FKSDB\Models\WebService\Models\StatsWebModel;
use FKSDB\Models\WebService\Models\WebModel;
use FKSDB\Models\WebService\WebServiceModel;
use FKSDB\Modules\Core\AuthMethod;
use Nette\Application\AbortException;
use Nette\Application\BadRequestException;
use Nette\Application\Routers\RouteList;
use Tracy\Debugger;

final class RestApiPresenter extends \FKSDB\Modules\Core\BasePresenter
{
    private const ROUTER = ['module' => 'Core', 'presenter' => 'RestApi', 'action' => 'default'];
    public const WEB_MODELS = [
        'GetFyziklaniResults' => \FKSDB\Models\WebService\Models\Game\ResultsWebModel::class,
        // 'game/submit' => Game\SubmitWebModel::class,
        'GetOrganizers' => OrganizersWebModel::class,
        'GetEventList' => EventListWebModel::class,
        'GetEvent' => EventWebModel::class,
        'GetSignatures' => SignaturesWebModel::class,
        'GetResults' => ResultsWebModel::class,
        'GetStats' => StatsWebModel::class,
        'GetPaymentList' => PaymentListWebModel::class,
        'GetSeriesResults' => SeriesResultsWebModel::class,
        'GetContests' => ContestsModel::class,
        // events
        'events' => EventListWebModel::class,

        // game
        'game/results' => \FKSDB\Models\WebService\Models\Game\ResultsWebModel::class,
        //'game/submit' => Game\SubmitWebModel::class,
    ];

    private WebServiceModel $server;
    /** @persistent */
    public string $model;

    final public function injectSoapServer(WebServiceModel $server): void
    {
        $this->server = $server;
    }

    /* TODO */
    public function authorizedDefault(): bool
    {
        return $this->contestAuthorizator->isAllowed('webService', 'default');
    }

    /**
     * @throws BadRequestException
     * @throws \ReflectionException
     * @throws \Throwable
     */
    final protected function beforeRender(): void
    {
        $params = [];
        if ($this->getHttpRequest()->getHeader('content-type') === 'application/json') {
            $params = json_decode($this->getHttpRequest()->getRawBody(), true);
        }
        try {
            $response = $this->server->getJsonResponse(
                $this->getWebModel(),
                array_merge($params, $this->getParameters())
            );
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

    /**
     * @throws \ReflectionException
     * @phpstan-return WebModel<array<string,mixed>,array<string,mixed>>
     */
    private function getWebModel(): ?WebModel
    {
        $webModelClass = class_exists($this->model)
            ? $this->model
            : (self::WEB_MODELS[$this->model] ?? self::WEB_MODELS[ucfirst($this->model)] ?? null);
        if ($webModelClass) {
            $reflection = new \ReflectionClass($webModelClass);
            if (!$reflection->isSubclassOf(WebModel::class)) {
                return null;
            }
            /** @phpstan-var WebModel<array<string,mixed>,array<string,mixed>> $model */
            $model = $reflection->newInstance($this->getContext());
            $model->setUser($this->getUser());
            return $model;
        }
        return null;
    }

    public static function createRouter(RouteList $list): void
    {
        /*   $list->addRoute(
               'events/<eventId [0-9]+>/schedule/group',
               array_merge(self::ROUTER, [
                   'model' => GroupListWebModel::class,
               ])
           );
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
                'model' => TeamsWebModel::class,
            ])
        );
        $list->addRoute(
            'events/<eventId [0-9]+>/participants',
            array_merge(self::ROUTER, [
                'model' => ParticipantsWebModel::class,
            ])
        );

        $list->addRoute(
            'events/<eventId [0-9]+>/',
            array_merge(self::ROUTER, [
                'model' => EventWebModel::class,
            ])
        );
        $list->addRoute(
            'events/',
            array_merge(self::ROUTER, [
                'model' => EventListWebModel::class,
            ])
        );
        $list->addRoute(
            'contests/',
            array_merge(self::ROUTER, [
                'model' => ContestsModel::class,
            ])
        );
        $list->addRoute(
            'contests/<contestId [0-9]+>/organizers',
            array_merge(self::ROUTER, [
                'model' => OrganizersWebModel::class,
            ])
        );
        $list->addRoute(
            'contests/<contestId [0-9]+>/years/<year [0-9]+>/stats',
            array_merge(self::ROUTER, [
                'model' => StatsWebModel::class,
            ])
        );
        $list->addRoute(
            '<model [a-zA-Z\./]+>',
            self::ROUTER
        );
    }
}
