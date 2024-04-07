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
    final public function __construct(Container $container, ?array $arguments)
    {
        $this->container = $container;
        $container->callInjects($this);
        if (isset($arguments)) {
            $processor = new Processor();
            $schema = new Structure($this->getInnerExpectedStructure());
            $schema->otherItems()->castTo('array');
            $this->params = $processor->process($schema, $arguments);
        }
    }

    public function injectAuthorizators(
        EventAuthorizator $eventAuthorizator,
        ContestAuthorizator $contestAuthorizator,
        BaseAuthorizator $baseAuthorizator,
        ContestYearAuthorizator $contestYearAuthorizator
    ): void {
        $this->eventAuthorizator = $eventAuthorizator;
        $this->contestAuthorizator = $contestAuthorizator;
        $this->baseAuthorizator = $baseAuthorizator;
        $this->contestYearAuthorizator = $contestYearAuthorizator;
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
    abstract protected function getInnerExpectedStructure(): array;

    /**
     * @throws GoneException
     * @phpstan-return TReturn
     */
    abstract protected function getJsonResponse(): array;

    abstract protected function isAuthorized(): bool;
}
