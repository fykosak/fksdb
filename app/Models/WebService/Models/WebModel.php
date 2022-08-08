<?php

declare(strict_types=1);

namespace FKSDB\Models\WebService\Models;

use FKSDB\Models\Exceptions\GoneException;
use FKSDB\Models\Exceptions\NotImplementedException;
use FKSDB\Models\ORM\Models\LoginModel;
use Nette\DI\Container;
use Nette\Schema\Elements\Structure;
use Nette\SmartObject;
use Tracy\Debugger;

abstract class WebModel
{
    use SmartObject;

    protected Container $container;
    protected ?LoginModel $authenticatedLogin;

    final public function __construct(Container $container)
    {
        $this->container = $container;
        $container->callInjects($this);
    }

    final public function setLogin(?LoginModel $authenticatedLogin): void
    {
        $this->authenticatedLogin = $authenticatedLogin;
    }

    /**
     * @throws GoneException
     */
    public function getResponse(\stdClass $args): \SoapVar
    {
        throw new GoneException();
    }

    protected function log(string $msg): void
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
     * @throws GoneException
     */
    public function getJsonResponse(array $params): array
    {
        throw new GoneException();
    }

    /**
     * @throws NotImplementedException
     */
    public function getExpectedParams(): Structure
    {
        throw new NotImplementedException();
    }
}
