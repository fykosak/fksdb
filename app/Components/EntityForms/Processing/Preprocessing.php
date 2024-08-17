<?php

declare(strict_types=1);

namespace FKSDB\Components\EntityForms\Processing;

use Fykosak\NetteORM\Model\Model;
use Nette\DI\Container;
use Nette\Forms\Form;

/**
 * @phpstan-template TModel of Model
 * @phpstan-template TData of array
 */
abstract class Preprocessing
{
    protected Container $container;

    public function __construct(Container $container)
    {
        $this->container = $container;
        $container->callInjects($this);
    }

    /**
     * @phpstan-param TData $values
     * @phpstan-return TData
     * @phpstan-param Model|null $model
     */
    abstract public function __invoke(array $values, Form $form, ?Model $model): array;
}
