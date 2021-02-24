<?php

namespace FKSDB\Models\Transitions\Machine;

use Exception;
use FKSDB\Models\Exceptions\BadTypeException;
use FKSDB\Models\ORM\Services\AbstractServiceSingle;
use FKSDB\Models\Transitions\StateModel;
use FKSDB\Models\Transitions\Transition\Transition;
use FKSDB\Models\Transitions\Transition\UnavailableTransitionsException;
use LogicException;
use Nette\Application\ForbiddenRequestException;
use Nette\Database\Explorer;
use Nette\Database\Table\ActiveRow;

/**
 * Class Machine
 * @author Michal Červeňák <miso@fykos.cz>
 */
abstract class Machine {

    public const STATE_INIT = '__init';
    public const STATE_TERMINATED = '__terminated';
    private array $transitions = [];
    protected Explorer $explorer;
    private AbstractServiceSingle $service;
    /**
     * @var callable
     * if callback return true, transition is allowed explicit, independently of transition's condition
     */
    private $explicitCondition;

    public function __construct(Explorer $explorer, AbstractServiceSingle $service) {
        $this->explorer=$explorer;
        $this->service = $service;
    }

    public function addTransition(Transition $transition): void {
        $this->transitions[] = $transition;
    }

    /**
     * @return Transition[]
     */
    public function getTransitions(): array {
        return $this->transitions;
    }

    /**
     * @param StateModel|null $model
     * @return Transition[]
     */
    public function getAvailableTransitions(?StateModel $model = null): array {
        $state = $model ? $model->getState() : null;
        if (\is_null($state)) {
            $state = self::STATE_INIT;
        }
        return \array_filter($this->getTransitions(), function (Transition $transition) use ($model, $state): bool {
            return ($transition->getFromState() === $state) && $this->canExecute($transition, $model);
        });
    }

    /**
     * @param string $id
     * @param StateModel $model
     * @return Transition
     * @throws UnavailableTransitionsException
     */
    protected function findTransitionById(string $id, StateModel $model): Transition {
        $transitions = \array_filter($this->getAvailableTransitions($model), function (Transition $transition) use ($id): bool {
            return $transition->getId() === $id;
        });

        return $this->selectTransition($transitions);
    }

    /**
     * @param array $transitions
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

    public function setExplicitCondition(callable $condition): void {
        $this->explicitCondition = $condition;
    }

    protected function canExecute(Transition $transition, ?StateModel $model = null): bool {
        if ($this->explicitCondition && ($this->explicitCondition)($model)) {
            return true;
        }
        return $transition->canExecute($model);
    }
    /* ********** EXECUTION ******** */

    /**
     * @param string $id
     * @param StateModel $model
     * @return StateModel
     *
     * @throws ForbiddenRequestException
     * @throws UnavailableTransitionsException
     * @throws Exception
     */
    public function executeTransition(string $id, StateModel $model): StateModel {
        $transition = $this->findTransitionById($id, $model);
        if (!$this->canExecute($transition, $model)) {
            throw new ForbiddenRequestException(_('Prechod sa nedá vykonať'));
        }
        return $this->execute($transition, $model);
    }

    /**
     * @param Transition $transition
     * @param StateModel|null $model
     * @return StateModel
     * @throws BadTypeException
     * @throws Exception
     */
    private function execute(Transition $transition, ?StateModel $model = null): StateModel {
        if (!$this->explorer->getConnection()->getPdo()->inTransaction()) {
            $this->explorer->getConnection()->beginTransaction();
        }
        try {
            $transition->beforeExecute($model);
        } catch (Exception $exception) {
            $this->explorer->getConnection()->rollBack();
            throw $exception;
        }
        if (!$model instanceof StateModel) {
            throw new BadTypeException(StateModel::class, $model);
        }

        $this->explorer->getConnection()->commit();
        $model->updateState($transition->getToState());
        /* select from DB new (updated) model */

        // $newModel = $model;
        $newModel = $model->refresh($this->explorer, $this->explorer->getConventions());
        $transition->afterExecute($newModel);
        return $newModel;
    }

    /* ********** MODEL CREATING ******** */

    abstract public function getCreatingState(): string;

    /**
     * @return Transition
     * @throws UnavailableTransitionsException
     */
    private function getCreatingTransition(): Transition {
        $transitions = \array_filter($this->getTransitions(), function (Transition $transition): bool {
            return $transition->getFromState() === self::STATE_INIT && $transition->getToState() === $this->getCreatingState();
        });
        return $this->selectTransition($transitions);
    }

    /**
     * @return bool
     * @throws Exception
     */
    public function canCreate(): bool {
        return $this->canExecute($this->getCreatingTransition());
    }

    /**
     * @param array $data
     * @param AbstractServiceSingle $service
     * @return StateModel
     * @throws ForbiddenRequestException
     * @throws UnavailableTransitionsException
     * @throws Exception
     */
    public function createNewModel(array $data, AbstractServiceSingle $service): StateModel {
        $transition = $this->getCreatingTransition();
        if (!$this->canExecute($transition)) {
            throw new ForbiddenRequestException(_('Model sa nedá vytvoriť'));
        }
        /** @var StateModel|ActiveRow $model */
        $model = $service->createNewModel($data);
        return $this->execute($transition, $model);
    }
}
