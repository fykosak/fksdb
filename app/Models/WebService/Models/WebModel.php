<?php

declare(strict_types=1);

namespace FKSDB\Models\WebService\Models;

use FKSDB\Models\Exceptions\GoneException;
use FKSDB\Models\Exceptions\NotImplementedException;
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
            $message = $this->user->getIdentity()->__toString() . '@'; // @phpstan-ignore-line
        }
        $message .= $_SERVER['REMOTE_ADDR'] . "\t" . $msg;
        Debugger::log($message, 'soap');
    }

    /**
     * @throws GoneException
     * @phpstan-param TParams $params
     * @phpstan-return TReturn
     */
    protected function getJsonResponse(array $params): array
    {
        throw new GoneException();
    }

    /**
     * @phpstan-param TParams $arguments
     * @throws GoneException
     * @throws NotImplementedException
     */
    final public function getApiResponse(array $arguments): JsonResponse
    {
        $arguments = $this->processArguments($arguments);
        return new JsonResponse($this->getJsonResponse($arguments));
    }

    /**
     * @throws NotImplementedException
     */
    public function getExpectedParams(): Structure
    {
        throw new NotImplementedException();
    }

    /**
     * @throws NotImplementedException
     * @phpstan-param TParams $arguments
     * @phpstan-return TParams
     */
    final public function processArguments(array $arguments): array
    {
        $processor = new Processor();
        $schema = $this->getExpectedParams();
        $schema->otherItems()->castTo('array');
        return $processor->process($schema, $arguments);
    }
}
