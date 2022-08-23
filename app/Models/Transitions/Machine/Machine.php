<?php

declare(strict_types=1);

namespace FKSDB\Models\Transitions\Machine;

use FKSDB\Models\ORM\Columns\Types\EnumColumn;
use Fykosak\NetteORM\Model;
use FKSDB\Models\Transitions\Holder\ModelHolder;
use FKSDB\Models\Transitions\Transition\Transition;
use FKSDB\Models\Transitions\Transition\UnavailableTransitionsException;
use Nette\Application\ForbiddenRequestException;
use Nette\Database\Explorer;

abstract class Machine extends AbstractMachine
{

    protected Explorer $explorer;
    /**
     * @var callable|null
     * if callback return true, transition is allowed explicit, independently of transition's condition
     */
    private $implicitCondition = null;

    public function __construct(Explorer $explorer)
    {
        $this->explorer = $explorer;
    }

    final public function setImplicitCondition(callable $implicitCondition): void
    {
        $this->implicitCondition = $implicitCondition;
    }
    /* **************** Select transition ****************/


    /**
     * @return Transition[]
     */
    public function getAvailableTransitions(ModelHolder $holder): array
    {
        return \array_filter(
            $this->getTransitions(),
            fn(Transition $transition): bool => $this->isAvailable($transition, $holder)
        );
    }

    /**
     * @throws UnavailableTransitionsException
     */
    public function getTransitionById(string $id): Transition
    {
        $transitions = \array_filter(
            $this->getTransitions(),
            fn(Transition $transition): bool => $transition->getId() === $id
        );
        return $this->selectTransition($transitions);
    }

    public function getTransitionByStates(?EnumColumn $source, ?EnumColumn $target): ?Transition
    {
        $transitions = \array_filter(
            $this->getTransitions(),
            function (Transition $transition) use ($target, $source): bool {
                $matchSource = is_null($source) && is_null($transition->sourceStateEnum) ||
                    ($source && $transition->sourceStateEnum &&
                        ($source->value === $transition->sourceStateEnum->value));
                $matchTarget = is_null($target) && is_null($transition->targetStateEnum) ||
                    ($target && $transition->targetStateEnum &&
                        ($target->value === $transition->targetStateEnum->value));
                return $matchSource && $matchTarget;
            }
        );
        return $this->selectTransition($transitions);
    }

    private function isAvailable(Transition $transition, ModelHolder $holder): bool
    {
        return $transition->matchSource($holder->getState()) && $this->canExecute($transition, $holder);
    }

    /**
     * @param Transition[] $transitions
     * @throws \LogicException
     * @throws UnavailableTransitionsException
     * Protect more that one transition between nodes
     */
    private function selectTransition(array $transitions): Transition
    {
        $length = \count($transitions);
        if ($length > 1) {
            throw new UnavailableTransitionsException();
        }
        if (!$length) {
            throw new UnavailableTransitionsException();
        }
        return \array_values($transitions)[0];
    }

    /* ********** execution ******** */

    /**
     * @throws UnavailableTransitionsException
     * @throws \Throwable
     */
    final public function executeTransitionById(string $id, ModelHolder $holder): void
    {
        $transition = $this->getTransitionById($id);
        if (!$this->isAvailable($transition, $holder)) {
            throw new UnavailableTransitionsException();
        }
        $this->execute($transition, $holder);
    }

    /**
     * @throws ForbiddenRequestException
     * @throws UnavailableTransitionsException
     * @throws \Throwable
     */
    final public function executeImplicitTransition(ModelHolder $holder): void
    {
        $transition = $this->selectTransition($this->getAvailableTransitions($holder));
        $this->execute($transition, $holder);
    }

    protected function canExecute(Transition $transition, ModelHolder $holder): bool
    {
        if (isset($this->implicitCondition) && ($this->implicitCondition)($holder)) {
            return true;
        }
        return $transition->canExecute2($holder);
    }

    /**
     * @throws ForbiddenRequestException
     * @throws \Throwable
     */
    private function execute(Transition $transition, ModelHolder $holder): void
    {
        if (!$this->canExecute($transition, $holder)) {
            throw new ForbiddenRequestException(_('Prechod sa nedá vykonať'));
        }
        if (!$this->explorer->getConnection()->getPdo()->inTransaction()) {
            $this->explorer->getConnection()->beginTransaction();
        }
        try {
            $transition->callBeforeExecute($holder);
        } catch (\Throwable $exception) {
            $this->explorer->getConnection()->rollBack();
            throw $exception;
        }
        $this->explorer->getConnection()->commit();
        $holder->updateState($transition->targetStateEnum);
        $transition->callAfterExecute($holder);
    }

    abstract public function createHolder(?Model $model): ModelHolder;
}
