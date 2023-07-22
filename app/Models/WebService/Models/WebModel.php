<?php

declare(strict_types=1);

namespace FKSDB\Models\WebService\Models;

use FKSDB\Models\Exceptions\GoneException;
use FKSDB\Models\Exceptions\NotImplementedException;
use Nette\DI\Container;
use Nette\Schema\Elements\Structure;
use Nette\Security\User;
use Nette\SmartObject;
use Tracy\Debugger;

abstract class WebModel
{
    use SmartObject;

    protected Container $container;
    protected User $user;

    final public function __construct(Container $container)
    {
        $this->container = $container;
        $container->callInjects($this);
    }

    final public function setUser(User $user): void
    {
        $this->user = $user;
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
        if (!$this->user->isLoggedIn()) {
            $message = 'unauthenticated@';
        } else {
            $message = $this->user->getIdentity()->__toString() . '@';
        }
        $message .= $_SERVER['REMOTE_ADDR'] . "\t" . $msg;
        Debugger::log($message, 'soap');
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
