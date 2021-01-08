<?php

namespace FKSDB\Models\Transitions\Machine;

use Exception;
use FKSDB\Models\ORM\Models\AbstractModelSingle;
use FKSDB\Models\Transitions\Holder\ModelHolder;
use FKSDB\Models\ORM\Services\AbstractServiceSingle;
use FKSDB\Models\Transitions\Transition\Transition;
use FKSDB\Models\Transitions\Transition\UnavailableTransitionsException;
use LogicException;
use Nette\Application\ForbiddenRequestException;
use Nette\Database\Explorer;

/**
 * Class Machine
 * @author Michal Červeňák <miso@fykos.cz>
 */
abstract class Machine {

    public const STATE_INIT = '__init';
    public const STATE_TERMINATED = '__terminated';
    public const STATE_ANY = '*';
    /** @var Transition[] */
    private array $transitions = [];
    protected Explorer $explorer;
    private AbstractServiceSingle $service;
    /**
     * @var callable|null
     * if callback return true, transition is allowed explicit, independently of transition's condition
     */
    private $implicitCondition = null;

    public function __construct(Explorer $explorer, AbstractServiceSingle $service) {
        $this->explorer = $explorer;
        $this->service = $service;
    }

    final public function addTransition(Transition $transition): void {
        $this->transitions[] = $transition;
    }

    final public function setImplicitCondition(callable $implicitCondition): void {
        $this->implicitCondition = $implicitCondition;
    }

    /**
     * @return Transition[]
     */
    public function getTransitions(): array {
        return $this->transitions;
    }

    /**
     * @param ModelHolder|null $holder
     * @return Transition[]
     */
    public function getAvailableTransitions(?ModelHolder $holder): array {
        $state = $holder ? $holder->getState() : null;
        if (\is_null($state)) {
            $state = self::STATE_INIT;
        }
        return \array_filter($this->getTransitions(), function (Transition $transition) use ($holder, $state): bool {
            return $transition->matchSource($state) && $this->canExecute($transition, $holder);
        });
    }

    /**
     * @param string $id
     * @param ModelHolder $holder
     * @return Transition
     * @throws UnavailableTransitionsException
     */
    public function getAvailableTransitionById(string $id, ModelHolder $holder): Transition {
        $transitions = \array_filter($this->getAvailableTransitions($holder), function (Transition $transition) use ($id): bool {
            return $transition->getId() === $id;
        });

        return $this->selectTransition($transitions);
    }

    /**
     * @param string $id
     * @return Transition
     * @throws UnavailableTransitionsException
     */
    public function getTransitionById(string $id): Transition {
        $transitions = \array_filter($this->getTransitions(), function (Transition $transition) use ($id): bool {
            return $transition->getId() === $id;
        });
        return $this->selectTransition($transitions);
    }

    /**
     * @param Transition[] $transitions
     * @return Transition
     * @throws LogicException
     * @throws UnavailableTransitionsException
     * Protect more that one transition between nodes
     */
    private function selectTransition(array $transitions): Transition {
        $length = \count($transitions);
        if ($length > 1) {
            throw new UnavailableTransitionsException();
        }
        if (!$length) {
            throw new UnavailableTransitionsException();
        }
        return \array_values($transitions)[0];
    }

    /* ********** CONDITION ******** */

    protected function canExecute(Transition $transition, ?ModelHolder $holder): bool {
        if (isset($this->implicitCondition) && ($this->implicitCondition)($holder)) {
            return true;
        }
        return $transition->canExecute2($holder);
    }
    /* ********** EXECUTION ******** */

    /**
     * @param string $id
     * @param ModelHolder $holder
     * @return ModelHolder
     * @throws ForbiddenRequestException
     * @throws UnavailableTransitionsException
     * @throws Exception
     */
    final public function executeTransition(string $id, ModelHolder $holder): ModelHolder {
        $transition = $this->getAvailableTransitionById($id, $holder);
        if (!$this->canExecute($transition, $holder)) {
            throw new ForbiddenRequestException(_('Prechod sa nedá vykonať'));
        }
        return $this->execute($transition, $holder);
    }

    /**
     * @param Transition $transition
     * @param ModelHolder|null $holder
     * @return ModelHolder new Model holder after execution
     * @throws Exception
     */
    private function execute(Transition $transition, ModelHolder $holder): ModelHolder {
        if (!$this->explorer->getConnection()->getPdo()->inTransaction()) {
            $this->explorer->getConnection()->beginTransaction();
        }
        try {
            $transition->callBeforeExecute($holder);
        } catch (Exception $exception) {
            $this->explorer->getConnection()->rollBack();
            throw $exception;
        }
        $this->explorer->getConnection()->commit();
        $newHolder = $holder->updateState($transition->getTargetState());
        $transition->callAfterExecute($newHolder);
        return $newHolder;
    }

    /* ********** MODEL CREATING ******** */

    abstract public function getCreatingState(): string;

    /**
     * @return Transition
     * @throws UnavailableTransitionsException
     */
    final protected function getCreatingTransition(): Transition {
        $transitions = \array_filter($this->getTransitions(), function (Transition $transition) {
            return $transition->getSourceState() === self::STATE_INIT && $transition->getTargetState() === $this->getCreatingState();
        });
        return $this->selectTransition($transitions);
    }

    /**
     * @return bool
     * @throws Exception
     */
    public function canCreate(): bool {
        return $this->canExecute($this->getCreatingTransition(), null);
    }

    /**
     * @param array $data
     * @param AbstractServiceSingle $service
     * @return ModelHolder
     * @throws ForbiddenRequestException
     * @throws UnavailableTransitionsException
     * @throws Exception
     */
    public function initNewModel(array $data, AbstractServiceSingle $service): ModelHolder {
        $transition = $this->getCreatingTransition();
        if (!$this->canExecute($transition, null)) {
            throw new ForbiddenRequestException(_('Model sa nedá vytvoriť'));
        }
        $model = $service->createNewModel($data);
        return $this->execute($transition, $this->createHolder($model));
    }

    abstract public function createHolder(AbstractModelSingle $model): ModelHolder;
}
