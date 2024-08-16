<?php

declare(strict_types=1);

namespace FKSDB\Components\EntityForms\Processing;

use FKSDB\Models\ORM\Columns\Types\EnumColumn;
use FKSDB\Models\Transitions\Holder\ModelHolder;
use FKSDB\Models\Transitions\Machine\Machine;
use FKSDB\Models\Utils\FakeStringEnum;
use Fykosak\NetteORM\Model\Model;
use Nette\DI\Container;

/**
 * @phpstan-template TModel of Model
 * @phpstan-extends Postprocessing<TModel>
 * @phpstan-type TMachine = Machine<ModelHolder<TModel,FakeStringEnum&EnumColumn>>
 */
final class DefaultTransition extends Postprocessing
{
    /**
     * @phpstan-var TMachine $machine
     */
    private Machine $machine;

    /**
     * @phpstan-param TMachine $machine
     */
    public function __construct(Container $container, Machine $machine)
    {
        parent::__construct($container);
        $this->machine = $machine;
    }

    /**
     * @throws \Throwable
     */
    public function __invoke(Model $model): void
    {
        $holder = $this->machine->createHolder($model);
        // ak je vykoná defaultny prechod
        $transition = $this->machine->getTransitions()->filterAvailable($holder)->select();
        $transition->execute($holder);
    }
}