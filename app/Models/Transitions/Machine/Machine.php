<?php

declare(strict_types=1);

namespace FKSDB\Models\Transitions\Machine;

use FKSDB\Models\ORM\Columns\Types\EnumColumn;
use FKSDB\Models\Transitions\Holder\ModelHolder;
use FKSDB\Models\Transitions\Transition\Transition;
use FKSDB\Models\Transitions\Transition\UnavailableTransitionsException;
use FKSDB\Models\Transitions\TransitionsDecorator;
use Fykosak\NetteORM\Model;
use Nette\Application\ForbiddenRequestException;
use Nette\Database\Explorer;

abstract class Machine
{
    public const STATE_INIT = '__init';
    public const STATE_ANY = '*';

    /** @var Transition[] */
    protected array $transitions = [];
    protected Explorer $explorer;

    public function __construct(Explorer $explorer)
    {
        $this->explorer = $explorer;
    }

    public function addTransition(Transition $transition): void
    {
        $this->transitions[] = $transition;
    }

    /**
     * @return Transition[]
     */
    public function getTransitions(): array
    {
        return $this->transitions;
    }

    /**
     * @throws UnavailableTransitionsException
     */
    public function getTransitionById(string $id): Transition
    {
        $transitions = \array_filter(
            $this->transitions,
            fn(Transition $transition): bool => $transition->getId() === $id
        );
        return $this->selectTransition($transitions);
    }

    /**
     * @param Transition[] $transitions
     * @throws \LogicException
     * @throws UnavailableTransitionsException
     * Protect more that one transition between nodes
     */
    protected function selectTransition(array $transitions): Transition
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

    final public function decorateTransitions(?TransitionsDecorator $decorator): void
    {
        if ($decorator) {
            $decorator->decorate($this);
        }
    }

    /**
     * @return Transition[]
     */
    public function getAvailableTransitions(ModelHolder $holder): array
    {
        return \array_filter(
            $this->transitions,
            fn(Transition $transition): bool => $this->isAvailable($transition, $holder)
        );
    }

    public function getTransitionByStates(EnumColumn $source, EnumColumn $target): Transition
    {
        $transitions = \array_filter(
            $this->transitions,
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
        if ($transition->source->value !== $holder->getState()->value) {
            return false;
        }
        return $transition->canExecute($holder);
    }

    /**
     * @throws UnavailableTransitionsException
     * @throws \Throwable
     */
    public function execute(Transition $transition, ModelHolder $holder): void
    {
        if (!$this->isAvailable($transition, $holder)) {
            throw new UnavailableTransitionsException();
        }
        $outerTransition = true;
        if (!$this->explorer->getConnection()->getPdo()->inTransaction()) {
            $outerTransition = false;
            $this->explorer->getConnection()->beginTransaction();
        }
        try {
            $transition->callBeforeExecute($holder);
            $holder->updateState($transition->target);
            $transition->callAfterExecute($holder);
        } catch (\Throwable $exception) {
            if (!$outerTransition) {
                $this->explorer->getConnection()->rollBack();
            }
            throw $exception;
        }
        if (!$outerTransition) {
            $this->explorer->getConnection()->commit();
        }
    }

    abstract public function createHolder(Model $model): ModelHolder;
}
