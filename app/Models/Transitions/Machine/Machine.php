<?php

declare(strict_types=1);

namespace FKSDB\Models\Transitions\Machine;

use FKSDB\Models\Transitions\Holder\ModelHolder;
use FKSDB\Models\Transitions\Transition\Transition;
use FKSDB\Models\Transitions\Transition\UnavailableTransitionException;
use FKSDB\Models\Transitions\TransitionsDecorator;
use Fykosak\NetteORM\Model\Model;
use Nette\Database\Explorer;

/**
 * @phpstan-template THolder of ModelHolder
 * @phpstan-import-type Enum from Transition
 */
abstract class Machine
{
    /** @phpstan-var Transition<THolder>[] */
    protected array $transitions = [];
    protected Explorer $explorer;

    public function __construct(Explorer $explorer)
    {
        $this->explorer = $explorer;
    }

    /**
     * @phpstan-param Transition<THolder> $transition
     */
    final public function addTransition(Transition $transition): void
    {
        $this->transitions[] = $transition;
    }

    /**
     * @phpstan-return TransitionsSelection<THolder>
     */
    public function getTransitionsSelection(): TransitionsSelection
    {
        return new TransitionsSelection($this->transitions);
    }

    /**
     * @phpstan-param TransitionsDecorator<THolder>|null $decorator
     */
    final public function decorateTransitions(?TransitionsDecorator $decorator): void
    {
        if ($decorator) {
            $decorator->decorate($this);
        }
    }

    /**
     * @throws UnavailableTransitionException
     * @throws \Throwable
     * @phpstan-param THolder $holder
     * @phpstan-param Transition<THolder> $transition
     */
    public function execute(Transition $transition, ModelHolder $holder): void
    {
        if (!$transition->canExecute($holder)) {
            throw new UnavailableTransitionException($transition, $holder);
        }
        $outerTransition = true;
        if (!$this->explorer->getConnection()->getPdo()->inTransaction()) {
            $outerTransition = false;
            $this->explorer->getConnection()->beginTransaction();
        }
        try {
            $transition->callBeforeExecute($holder);
            $holder->setState($transition->target);
            $transition->callAfterExecute($holder);
        } catch (\Throwable $exception) {
            if (!$outerTransition) {
                $this->explorer->getConnection()->rollBack();
            }
            if (count($transition->onFail)) {
                foreach ($transition->onFail as $failHandler) {
                    $failHandler->handle($exception, $holder, $transition);
                }
            } else {
                throw $exception;
            }
        }
        if (!$outerTransition) {
            $this->explorer->getConnection()->commit();
        }
    }

    /**
     * @phpstan-return THolder
     */
    abstract public function createHolder(Model $model): ModelHolder;
}
