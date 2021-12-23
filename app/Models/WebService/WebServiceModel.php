<?php

namespace FKSDB\Models\WebService;

use FKSDB\Models\Authentication\PasswordAuthenticator;
use FKSDB\Models\ORM\Models\ModelLogin;
use FKSDB\Models\WebService\Models\WebModel;
use Nette\Application\BadRequestException;
use Nette\Application\Responses\JsonResponse;
use Nette\DI\Container;
use Nette\Http\IResponse;
use Nette\Security\AuthenticationException;
use Tracy\Debugger;

class WebServiceModel
{

    private ModelLogin $authenticatedLogin;
    private PasswordAuthenticator $authenticator;
    private Container $container;

    private const WEB_MODELS = [
        'GetOrganizers' => Models\OrganizersWebModel::class,
        'GetEventList' => Models\EventListWebModel::class,
        'GetEvent' => Models\EventWebModel::class,
        'GetExport' => Models\ExportWebModel::class,
        'GetSignatures' => Models\SignaturesWebModel::class,
        'GetResults' => Models\ResultsWebModel::class,
        'GetStats' => Models\StatsWebModel::class,
    ];

    public function __construct(Container $container, PasswordAuthenticator $authenticator)
    {
        $this->authenticator = $authenticator;
        $this->container = $container;
    }

    /**
     * This method should be called when handling AuthenticationCredentials SOAP header.
     *
     * @param \stdClass $args
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
            $this->authenticatedLogin = $this->authenticator->authenticate($args->username, $args->password);
            $this->log('Successfully authenticated for web service request.');
        } catch (AuthenticationException $exception) {
            $this->log('Invalid credentials.');
            throw new \SoapFault('Sender', 'Invalid credentials.');
        }
    }

    /**
     * @param string $name
     * @param \stdClass[] $arguments
     * @return \SoapVar
     * @throws \SoapFault
     * @throws \ReflectionException
     */
    public function __call(string $name, array $arguments): \SoapVar
    {
        $this->checkAuthentication(__FUNCTION__);
        $webModel = $this->getWebModel($name);
        if (!$webModel) {
            throw new \SoapFault('Server', 'Undefined method');
        }
        return $webModel->getResponse(...$arguments);
    }

    /**
     * @param string $serviceName
     * @throws \SoapFault
     */
    private function checkAuthentication(string $serviceName): void
    {
        if (!isset($this->authenticatedLogin)) {
            $msg = sprintf('Unauthenticated access to %s.', $serviceName);
            $this->log($msg);
            throw new \SoapFault('Sender', $msg);
        } else {
            $this->log(sprintf('Called %s ', $serviceName));
        }
    }

    private function log(string $msg): void
    {
        if (!isset($this->authenticatedLogin)) {
            $message = 'unauthenticated@';
        } else {
            $message = $this->authenticatedLogin->__toString() . '@';
        }
        $message .= $_SERVER['REMOTE_ADDR'] . "\t" . $msg;
        Debugger::log($message);
    }

    /**
     * @param string $name
     * @return WebModel
     * @throws \ReflectionException
     */
    private function getWebModel(string $name): ?WebModel
    {
        $name = ucfirst($name);
        if (isset(self::WEB_MODELS[$name])) {
            $reflection = new \ReflectionClass(self::WEB_MODELS[$name]);
            if (!$reflection->isSubclassOf(WebModel::class)) {
                return null;
            }
            return $reflection->newInstance($this->container);
        }
        return null;
    }

    /**
     * @param string $name
     * @param array $arguments
     * @return JsonResponse
     * @throws \ReflectionException|BadRequestException
     */
    public function getJsonResponse(string $name, array $arguments): JsonResponse
    {
        $webModel = $this->getWebModel($name);
        if (!$webModel) {
            throw new BadRequestException('Undefined method', IResponse::S404_NOT_FOUND);
        }
        //$webModel->setLogin($this->authenticatedLogin);
        return new JsonResponse($webModel->getJsonResponse($arguments));
    }
}
