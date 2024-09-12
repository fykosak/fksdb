<?php

declare(strict_types=1);

namespace FKSDB\Components\EntityForms\Processing;

use Fykosak\NetteORM\Model\Model;
use Nette\DI\Container;

/**
 * @phpstan-template TModel of Model
 */
abstract class Postprocessing
{
    protected Container $container;

    public function __construct(Container $container)
    {
        $this->container = $container;
        $container->callInjects($this);
    }

    /**
     * @phpstan-param TModel $model
     */
    abstract public function __invoke(Model $model): void;
}
