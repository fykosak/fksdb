<?php

declare(strict_types=1);

namespace FKSDB\Models\WebService;

use FKSDB\Models\Authentication\PasswordAuthenticator;
use FKSDB\Models\Authorization\Authorizators\ContestAuthorizator;
use FKSDB\Models\Exceptions\GoneException;
use FKSDB\Models\WebService\Models\{Contests\ContestsWebModel,
    Contests\OrganizersWebModel,
    Contests\StatsWebModel,
    Events\EventDetailWebModel,
    Events\EventListWebModel,
    ExportWebModel,
    Game,
    PaymentListWebModel,
    ResultsWebModel,
    SignaturesWebModel,
    WebModel};
use Nette\DI\Container;
use Nette\Security\AuthenticationException;
use Nette\Security\User;
use Nette\SmartObject;
use Tracy\Debugger;

class WebServiceModel
{
    use SmartObject;

    public const SOAP_RESOURCE_ID = 'soap';

    private PasswordAuthenticator $authenticator;
    private Container $container;
    private ContestAuthorizator $contestAuthorizator;
    private User $user;

    private const WEB_MODELS = [
        'GetFyziklaniResults' => Game\ResultsWebModel::class,
        'GetOrganizers' => OrganizersWebModel::class,
        'GetEventList' => EventListWebModel::class,
        'GetEvent' => EventDetailWebModel::class,
        'GetExport' => ExportWebModel::class,
        'GetSignatures' => SignaturesWebModel::class,
        'GetResults' => ResultsWebModel::class,
        'GetStats' => StatsWebModel::class,
        'GetPaymentList' => PaymentListWebModel::class,
        'GetSeriesResults' => ResultsWebModel::class,
        'GetContests' => ContestsWebModel::class,
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
            if (!$this->contestAuthorizator->isAllowedAnyContest(self::SOAP_RESOURCE_ID, 'default')) {
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
        if (!$this->contestAuthorizator->isAllowedAnyContest(self::SOAP_RESOURCE_ID, 'default')) {
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
     * @phpstan-return WebModel<array<mixed>,array<mixed>>
     */
    private function getWebModel(string $name): ?WebModel
    {
        $webModelClass = self::WEB_MODELS[$name] ?? self::WEB_MODELS[ucfirst($name)] ?? null;
        if ($webModelClass) {
            $reflection = new \ReflectionClass(self::WEB_MODELS[$name]);
            if (!$reflection->isSubclassOf(WebModel::class)) {
                return null;
            }
            /** @phpstan-var WebModel<array<mixed>,array<mixed>> $model */
            $model = $reflection->newInstance($this->container, null);
            return $model;
        }
        return null;
    }
}
