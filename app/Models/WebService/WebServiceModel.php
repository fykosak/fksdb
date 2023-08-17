<?php

declare(strict_types=1);

namespace FKSDB\Models\WebService;

use FKSDB\Models\Authentication\PasswordAuthenticator;
use FKSDB\Models\Authorization\ContestAuthorizator;
use FKSDB\Models\Exceptions\GoneException;
use FKSDB\Models\WebService\Models\{ContestsModel,
    EventListWebModel,
    EventWebModel,
    ExportWebModel,
    Game,
    OrganizersWebModel,
    PaymentListWebModel,
    ResultsWebModel,
    SeriesResultsWebModel,
    SignaturesWebModel,
    StatsWebModel,
    WebModel
};
use Nette\Application\BadRequestException;
use Nette\Application\Responses\JsonResponse;
use Nette\DI\Container;
use Nette\Http\IResponse;
use Nette\Security\AuthenticationException;
use Nette\Security\User;
use Nette\SmartObject;
use Tracy\Debugger;

class WebServiceModel
{
    use SmartObject;

    private PasswordAuthenticator $authenticator;
    private Container $container;
    private ContestAuthorizator $contestAuthorizator;
    private User $user;

    private const WEB_MODELS = [
        'GetFyziklaniResults' => Game\ResultsWebModel::class,
        'game/results' => Game\ResultsWebModel::class,
        'game/submit' => Game\SubmitWebModel::class,
        'contest.organizers' => OrganizersWebModel::class,
        'GetOrganizers' => OrganizersWebModel::class,
        'GetEventList' => EventListWebModel::class,
        'GetEvent' => EventWebModel::class,
        'GetExport' => ExportWebModel::class,
        'GetSignatures' => SignaturesWebModel::class,
        'GetResults' => ResultsWebModel::class,
        'GetStats' => StatsWebModel::class,
        'GetPaymentList' => PaymentListWebModel::class,
        'GetSeriesResults' => SeriesResultsWebModel::class,
        'GetContests' => ContestsModel::class,
    ];

    public function __construct(
        Container $container,
        PasswordAuthenticator $authenticator,
        ContestAuthorizator $contestAuthorizator,
        User $user
    ) {
        $this->authenticator = $authenticator;
        $this->container = $container;
        $this->contestAuthorizator = $contestAuthorizator;
        $this->user = $user;
    }

    /**
     * This method should be called when handling AuthenticationCredentials SOAP header.
     *
     * @throws \SoapFault
     * @throws \Exception
     */
    public function authenticationCredentials(\stdClass $args): void
    {
        if (!isset($args->username) || !isset($args->password)) {
            $this->log('Missing credentials.');
            throw new \SoapFault('Sender', 'Missing credentials.');
        }
        try {
            $login = $this->authenticator->authenticate($args->username, $args->password);
            $this->user->login($login);
            $this->log('Successfully authenticated for web service request.');
            if (!$this->contestAuthorizator->isAllowed('soap', 'default')) {
                $this->log('Unauthorized.');
                throw new \SoapFault('Sender', 'Unauthorized.');
            }
        } catch (AuthenticationException $exception) {
            $this->log('Invalid credentials.');
            throw new \SoapFault('Sender', 'Invalid credentials.');
        }
    }

    /**
     * @throws GoneException
     * @throws \ReflectionException
     * @throws \SoapFault
     * @phpstan-ignore-next-line
     */
    public function __call(string $name, array $args): \SoapVar
    {
        $this->checkAuthentication($name . ': ' . json_encode($args));
        $webModel = $this->getWebModel($name);

        if (!$webModel) {
            throw new \SoapFault('Server', 'Undefined method');
        }
        return $webModel->getResponse(...$args);
    }

    /**
     * @throws \SoapFault
     */
    private function checkAuthentication(string $nameService): void
    {
        if (!$this->user->isLoggedIn()) {
            $msg = sprintf('Unauthenticated access to %s.', $nameService);
            $this->log($msg);
            throw new \SoapFault('Sender', $msg);
        } else {
            $this->log(sprintf('Called %s ', $nameService));
        }
        if (!$this->contestAuthorizator->isAllowed('soap', 'default')) {
            $this->log(sprintf('Unauthorized %s ', $nameService));
            throw new \SoapFault('Sender', 'Unauthorized');
        }
    }

    private function log(string $msg): void
    {
        if (!$this->user->isLoggedIn()) {
            $message = 'unauthenticated@';
        } else {
            $message = $this->user->getIdentity()->__toString() . '@'; // @phpstan-ignore-line
        }
        $message .= $_SERVER['REMOTE_ADDR'] . "\t" . $msg;
        Debugger::log($message, 'soap');
    }

    /**
     * @throws \ReflectionException
     * @phpstan-return WebModel<array<string,mixed>,array<string,mixed>>
     */
    private function getWebModel(string $name): ?WebModel
    {
        $webModelClass = self::WEB_MODELS[$name] ?? self::WEB_MODELS[ucfirst($name)] ?? null;
        if ($webModelClass) {
            $reflection = new \ReflectionClass(self::WEB_MODELS[$name]);
            if (!$reflection->isSubclassOf(WebModel::class)) {
                return null;
            }
            /** @phpstan-var WebModel<array<string,mixed>,array<string,mixed>> $model */
            $model = $reflection->newInstance($this->container);
            $model->setUser($this->user);
            return $model;
        }
        return null;
    }

    /**
     * @throws \ReflectionException
     * @throws BadRequestException
     * @phpstan-param array<string,mixed> $arguments
     */
    public function getJsonResponse(string $name, array $arguments): JsonResponse
    {
        $webModel = $this->getWebModel($name);
        if (!$webModel) {
            throw new BadRequestException('Undefined method', IResponse::S404_NOT_FOUND);
        }
        return $webModel->getApiResponse($arguments);
    }
}
