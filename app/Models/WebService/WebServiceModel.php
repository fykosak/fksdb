<?php

declare(strict_types=1);

namespace FKSDB\Models\WebService;

use FKSDB\Models\Authentication\Authenticator;
use FKSDB\Models\Authorization\Authorizators\ContestAuthorizator;
use FKSDB\Models\WebService\Models\Contests\OrganizersWebModel;
use FKSDB\Models\WebService\Models\Events\EventDetailWebModel;
use FKSDB\Models\WebService\Models\Events\EventListWebModel;
use FKSDB\Models\WebService\Models\ExportWebModel;
use FKSDB\Models\WebService\Models\ResultsWebModel;
use FKSDB\Models\WebService\Models\SoapWebModel;
use FKSDB\Models\WebService\Models\WebModel;
use Nette\DI\Container;
use Nette\Security\AuthenticationException;
use Nette\Security\User;
use Nette\SmartObject;
use Tracy\Debugger;

final class WebServiceModel
{
    use SmartObject;

    public const SOAP_RESOURCE_ID = 'soap';

    private Authenticator $authenticator;
    private Container $container;
    private ContestAuthorizator $contestAuthorizator;
    private User $user;

    private const WEB_MODELS = [
        'GetOrganizers' => OrganizersWebModel::class,
        'GetEventList' => EventListWebModel::class,
        'GetEvent' => EventDetailWebModel::class,
        'GetExport' => ExportWebModel::class,
        'GetResults' => ResultsWebModel::class,
        'GetSeriesResults' => ResultsWebModel::class,
    ];

    public function __construct(
        Container $container,
        Authenticator $authenticator,
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
            $login = $this->authenticator->authenticatePassword($args->username, $args->password);
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
        return $webModel->getSOAPResponse(...$args);
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
     * @phpstan-return (WebModel<array<mixed>,array<mixed>>&SoapWebModel)|null
     */
    private function getWebModel(string $name): ?WebModel
    {
        $webModelClass = self::WEB_MODELS[$name] ?? self::WEB_MODELS[ucfirst($name)] ?? null;
        if ($webModelClass) {
            $reflection = new \ReflectionClass(self::WEB_MODELS[$name]);
            if (!$reflection->isSubclassOf(WebModel::class)) {
                return null;
            }
            if (!$reflection->isSubclassOf(SoapWebModel::class)) {
                return null;
            }
            /** @phpstan-var WebModel<array<mixed>,array<mixed>>&SoapWebModel $model */
            $model = $reflection->newInstance($this->container, null);
            return $model;
        }
        return null;
    }
}
