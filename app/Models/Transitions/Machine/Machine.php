<?php

declare(strict_types=1);

namespace FKSDB\Models\Transitions\Machine;

use Fykosak\NetteORM\AbstractModel;
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
        return \array_filter($this->getTransitions(), function (Transition $transition) use ($holder): bool {
            return $this->isAvailable($transition, $holder);
        });
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
     * @throws \Exception
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
     * @throws \Exception
     */
    final public function saveAndExecuteImplicitTransition(ModelHolder $holder, array $data): void
    {
        $transition = $this->selectTransition($this->getAvailableTransitions($holder));
        $this->saveAndExecuteTransition($transition, $holder, $data);
    }

    /**
     * @throws ForbiddenRequestException
     */
    final public function saveAndExecuteTransition(Transition $transition, ModelHolder $holder, array $data): void
    {
        $holder->updateData($data);
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
     * @throws \Exception
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
        } catch (\Exception $exception) {
            $this->explorer->getConnection()->rollBack();
            throw $exception;
        }
        $this->explorer->getConnection()->commit();
        $holder->updateState($transition->getTargetState());
        $transition->callAfterExecute($holder);
    }

    abstract public function createHolder(?AbstractModel $model): ModelHolder;
}
