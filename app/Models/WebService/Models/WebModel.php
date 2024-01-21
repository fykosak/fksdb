<?php

declare(strict_types=1);

namespace FKSDB\Models\WebService\Models;

use FKSDB\Models\Authorization\Authorizators\BaseAuthorizator;
use FKSDB\Models\Authorization\Authorizators\ContestAuthorizator;
use FKSDB\Models\Authorization\Authorizators\ContestYearAuthorizator;
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
    protected ContestYearAuthorizator $contestYearAuthorizator;
    /**
     * @phpstan-var TParams
     */
    protected array $params;

    /**
     * @throws NotImplementedException
     * @phpstan-param mixed[] $arguments
     */
    final public function __construct(Container $container, array $arguments)
    {
        $this->container = $container;
        $container->callInjects($this);
        $processor = new Processor();
        $schema = $this->getExpectedParams();
        $schema->otherItems()->castTo('array');
        $this->params = $processor->process($schema, $arguments);
    }

    public function injectAuthorizators(
        User $user,
        EventAuthorizator $eventAuthorizator,
        ContestAuthorizator $contestAuthorizator,
        BaseAuthorizator $baseAuthorizator,
        ContestYearAuthorizator $contestYearAuthorizator
    ): void {
        $this->user = $user;
        $this->eventAuthorizator = $eventAuthorizator;
        $this->contestAuthorizator = $contestAuthorizator;
        $this->baseAuthorizator = $baseAuthorizator;
        $this->contestYearAuthorizator = $contestYearAuthorizator;
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
     * @throws GoneException
     * @throws ForbiddenRequestException
     */
    final public function getApiResponse(): JsonResponse
    {
        if (!$this->isAuthorized()) {
            throw new ForbiddenRequestException();
        }
        return new JsonResponse($this->getJsonResponse());
    }

    /**
     * @throws NotImplementedException
     */
    abstract protected function getExpectedParams(): Structure;

    /**
     * @throws GoneException
     * @phpstan-return TReturn
     */
    abstract protected function getJsonResponse(): array;

    abstract protected function isAuthorized(): bool;
}
