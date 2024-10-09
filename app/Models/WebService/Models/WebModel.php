<?php

declare(strict_types=1);

namespace FKSDB\Models\WebService\Models;

use FKSDB\Models\Authorization\Authorizators\Authorizator;
use FKSDB\Models\Authorization\Authorizators\ContestAuthorizator;
use FKSDB\Models\Exceptions\GoneException;
use FKSDB\Models\Exceptions\NotImplementedException;
use Nette\Application\ForbiddenRequestException;
use Nette\Application\Responses\JsonResponse;
use Nette\DI\Container;
use Nette\Schema\Elements\Structure;
use Nette\Schema\Processor;
use Nette\Schema\Schema;
use Nette\SmartObject;

/**
 * @phpstan-template TParams of array
 * @phpstan-template TReturn of array
 */
abstract class WebModel
{
    use SmartObject;

    protected Container $container;

    protected ContestAuthorizator $contestAuthorizator;
    protected Authorizator $authorizator;
    /**
     * @phpstan-var TParams
     */
    protected array $params;

    /**
     * @throws NotImplementedException
     * @phpstan-param mixed[] $arguments
     */
    final public function __construct(Container $container, ?array $arguments)
    {
        $this->container = $container;
        $container->callInjects($this);
        if (isset($arguments)) {
            $processor = new Processor();
            $schema = new Structure($this->getExpectedParams());
            $schema->otherItems()->castTo('array');
            $this->params = $processor->process($schema, $arguments);
        }
    }

    public function injectAuthorizators(
        ContestAuthorizator $contestAuthorizator,
        Authorizator $authorizator
    ): void {
        $this->contestAuthorizator = $contestAuthorizator;
        $this->authorizator = $authorizator;
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
     * @phpstan-return Schema[]
     */
    abstract protected function getExpectedParams(): array;

    /**
     * @throws GoneException
     * @phpstan-return TReturn
     */
    abstract protected function getJsonResponse(): array;

    abstract protected function isAuthorized(): bool;
}
