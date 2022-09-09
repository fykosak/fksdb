<?php

declare(strict_types=1);

namespace FKSDB\Models\Transitions\Machine;

use FKSDB\Models\ORM\Columns\Types\EnumColumn;
use FKSDB\Models\Transitions\TransitionsDecorator;
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

    final public function decorateTransitions(TransitionsDecorator $decorator): void
    {
        $decorator->decorate($this);
    }

    final public function setImplicitCondition(callable $implicitCondition): void
    {
        $this->implicitCondition = $implicitCondition;
    }

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

    public function getTransitionByStates(EnumColumn $source, EnumColumn $target): ?Transition
    {
        $transitions = \array_filter(
            $this->getTransitions(),
            fn(Transition $transition): bool => ($source->value === $transition->source->value) &&
                ($target->value === $transition->target->value)
        );
        return $this->selectTransition($transitions);
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

    protected function isAvailable(Transition $transition, ModelHolder $holder): bool
    {
        if (!$transition->matchSource($holder->getState())) {
            return false;
        }
        if (isset($this->implicitCondition) && ($this->implicitCondition)($holder)) {
            return true;
        }
        return $transition->canExecute($holder);
    }

    /**
     * @throws ForbiddenRequestException
     * @throws \Throwable
     */
    private function execute(Transition $transition, ModelHolder $holder): void
    {
        if (!$this->isAvailable($transition, $holder)) {
            throw new ForbiddenRequestException(_('Prechod sa nedá vykonať'));
        }
        $outerTransition = true;
        if (!$this->explorer->getConnection()->getPdo()->inTransaction()) {
            $outerTransition = false;
            $this->explorer->getConnection()->beginTransaction();
        }
        try {
            $transition->callBeforeExecute($holder);
        } catch (\Throwable $exception) {
            $this->explorer->getConnection()->rollBack();
            throw $exception;
        }
        if (!$outerTransition) {
            $this->explorer->getConnection()->commit();
        }

        $holder->updateState($transition->target);
        $transition->callAfterExecute($holder);
    }

    abstract public function createHolder(Model $model): ModelHolder;
}
