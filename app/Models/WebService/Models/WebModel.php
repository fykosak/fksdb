<?php

declare(strict_types=1);

namespace FKSDB\Models\WebService\Models;

use FKSDB\Models\Authorization\Authorizators\BaseAuthorizator;
use FKSDB\Models\Authorization\Authorizators\ContestAuthorizator;
use FKSDB\Models\Authorization\Authorizators\EventAuthorizator;
use FKSDB\Models\Exceptions\GoneException;
use FKSDB\Models\Exceptions\NotImplementedException;
use Nette\Application\ForbiddenRequestException;
use Nette\Application\Responses\JsonResponse;
use Nette\DI\Container;
use Nette\Schema\Elements\Structure;
use Nette\Schema\Processor;
use Nette\Security\User;
use Nette\SmartObject;
use Tracy\Debugger;

/**
 * @phpstan-template TParams of array
 * @phpstan-template TReturn of array
 */
abstract class WebModel
{
    use SmartObject;

    protected Container $container;
    protected User $user;

    protected EventAuthorizator $eventAuthorizator;
    protected ContestAuthorizator $contestAuthorizator;
    protected BaseAuthorizator $baseAuthorizator;

    final public function __construct(Container $container)
    {
        $this->container = $container;
        $container->callInjects($this);
    }

    public function injectAuthorizators(
        User $user,
        EventAuthorizator $eventAuthorizator,
        ContestAuthorizator $contestAuthorizator,
        BaseAuthorizator $baseAuthorizator
    ): void {
        $this->user = $user;
        $this->eventAuthorizator = $eventAuthorizator;
        $this->contestAuthorizator = $contestAuthorizator;
        $this->baseAuthorizator = $baseAuthorizator;
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
            $message = $this->user->getIdentity()->__toString() . '@'; // @phpstan-ignore-line
        }
        $message .= $_SERVER['REMOTE_ADDR'] . "\t" . $msg;
        Debugger::log($message, 'soap');
    }


    /**
     * @phpstan-param TParams $arguments
     * @throws GoneException
     * @throws NotImplementedException
     * @throws ForbiddenRequestException
     */
    final public function getApiResponse(array $arguments): JsonResponse
    {
        $processor = new Processor();
        $schema = $this->getExpectedParams();
        $schema->otherItems()->castTo('array');
        $params = $processor->process($schema, $arguments);
        if (!$this->isAuthorized($params)) {
            throw new ForbiddenRequestException();
        }
        return new JsonResponse($this->getJsonResponse($params));
    }

    /**
     * @throws NotImplementedException
     */
    abstract protected function getExpectedParams(): Structure;

    /**
     * @throws GoneException
     * @phpstan-param TParams $params
     * @phpstan-return TReturn
     */
    abstract protected function getJsonResponse(array $params): array;

    /**
     * @phpstan-param TParams $params
     */
    abstract protected function isAuthorized(array $params): bool;
}
