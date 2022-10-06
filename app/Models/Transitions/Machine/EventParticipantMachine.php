<?php

declare(strict_types=1);

namespace FKSDB\Models\Transitions\Machine;

use FKSDB\Models\Events\EventDispatchFactory;
use FKSDB\Models\Events\Model\Holder\BaseHolder;
use FKSDB\Models\Expressions\NeonSchemaException;
use FKSDB\Models\ORM\Columns\Types\EnumColumn;
use FKSDB\Models\ORM\Models\EventParticipantModel;
use FKSDB\Models\Transitions\Holder\ModelHolder;
use FKSDB\Models\Transitions\Transition\Transition;
use FKSDB\Models\Transitions\Transition\UnavailableTransitionsException;
use Fykosak\NetteORM\Model;
use Nette\Database\Explorer;

/**
 * @property Transition[] $transitions
 */
class EventParticipantMachine extends Machine
{

    public string $name = 'participant';

    private EventDispatchFactory $eventDispatchFactory;

    public function __construct(EventDispatchFactory $eventDispatchFactory, Explorer $explorer)
    {
        parent::__construct($explorer);
        $this->eventDispatchFactory = $eventDispatchFactory;
    }

    /**
     * @param BaseHolder $holder
     * @return Transition[]
     */
    public function getAvailableTransitions(
        ModelHolder $holder,
        ?EnumColumn $source = null
    ): array {
        return array_filter(
            $this->geTransitionsBySource($source ?? $holder->getModelState()),
            fn(Transition $transition): bool => $transition->canExecute($holder)
        );
    }


    /**
     * @param EventParticipantModel $model
     * @throws NeonSchemaException
     */
    public function createHolder(Model $model): BaseHolder
    {
        $holder = $this->eventDispatchFactory->getDummyHolder($model->event);
        $holder->setModel($model);
        return $holder;
    }

    final public function execute2(
        Transition $transition,
        BaseHolder $holder
    ): void {
        if (!$transition->canExecute($holder)) {
            throw new UnavailableTransitionsException();
        }

        $holder->setModelState($transition->target);
    }
}
