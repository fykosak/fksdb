<?php

namespace FKSDB\Models\WebService;

use FKSDB\Models\Authentication\PasswordAuthenticator;
use FKSDB\Models\Exceptions\BadTypeException;
use FKSDB\Models\ORM\Models\ModelLogin;
use FKSDB\Models\WebService\Models\WebModel;
use Nette\DI\Container;
use Nette\Security\AuthenticationException;
use SoapFault;
use SoapVar;
use stdClass;
use Tracy\Debugger;

/**
 * Web service provider for fksdb.wdsl
 * @author michal
 */
class WebServiceModel {

    private ModelLogin $authenticatedLogin;
    private PasswordAuthenticator $authenticator;

    private Container $container;

    private array $webModels = [
        'GetOrganizers' => Models\OrganizersWebModel::class,
        'GetEventsList' => Models\EventsListWebModel::class,
        'GetEvent' => Models\EventWebModel::class,
        'GetExport' => Models\ExportWebModel::class,
        'GetSignatures' => Models\SignaturesWebModel::class,
        'GetResults' => Models\ResultsWebModel::class,
        'GetStats' => Models\StatsWebModel::class,
    ];

    public function __construct(Container $container, PasswordAuthenticator $authenticator) {
        $this->authenticator = $authenticator;
        $this->container = $container;
    }

    /**
     * This method should be called when handling AuthenticationCredentials SOAP header.
     *
     * @param stdClass $args
     * @throws SoapFault
     * @throws \Exception
     */
    public function authenticationCredentials(stdClass $args): void {
        if (!isset($args->username) || !isset($args->password)) {
            $this->log('Missing credentials.');
            throw new SoapFault('Sender', 'Missing credentials.');
        }
        try {
            $this->authenticatedLogin = $this->authenticator->authenticate($args->username, $args->password);
            $this->log('Successfully authenticated for web service request.');
        } catch (AuthenticationException $exception) {
            $this->log('Invalid credentials.');
            throw new SoapFault('Sender', 'Invalid credentials.');
        }
    }

    /**
     * @param string $name
     * @param \stdClass[] $arguments
     * @return SoapVar
     * @throws SoapFault
     * @throws BadTypeException
     */
    public function __call(string $name, array $arguments): SoapVar {
        $this->checkAuthentication(__FUNCTION__);
        if (isset($this->webModels[$name])) {
            $webModel = new $this->webModels[$name]($this->container);
            if (!$webModel instanceof WebModel) {
                throw new BadTypeException(Models\WebModel::class, $webModel);
            }
            $webModel->setLogin($this->authenticatedLogin);
            return $webModel->getResponse(...$arguments);
        }
        throw new SoapFault('Sender', 'Undefined method');
    }

    /**
     * @param string $serviceName
     * @throws SoapFault
     */
    private function checkAuthentication(string $serviceName): void {
        if (!isset($this->authenticatedLogin)) {
            $this->log("Unauthenticated access to $serviceName.");
            throw new SoapFault('Sender', "Unauthenticated access to $serviceName.");
        } else {
            $this->log(sprintf('Called %s ', $serviceName));
        }
    }

    private function log(string $msg): void {
        if (!isset($this->authenticatedLogin)) {
            $message = 'unauthenticated@';
        } else {
            $message = $this->authenticatedLogin->__toString() . '@';
        }
        $message .= $_SERVER['REMOTE_ADDR'] . "\t" . $msg;
        Debugger::log($message);
    }
}
