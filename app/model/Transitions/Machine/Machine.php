<?php

namespace FKSDB\Transitions;

use Exception;
use FKSDB\Exceptions\BadTypeException;
use FKSDB\ORM\IModel;
use FKSDB\ORM\IService;
use LogicException;
use Nette\Application\ForbiddenRequestException;
use Nette\Database\Context;
use Nette\Database\Table\ActiveRow;

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

    protected Context $context;

    private IService $service;
    /**
     * @var callable|null
     * if callback return true, transition is allowed explicit, independently of transition's condition
     */
    private $implicitCondition = null;

    /**
     * Machine constructor.
     * @param Context $context
     * @param IService $service
     */
    public function __construct(Context $context, IService $service) {
        $this->context = $context;
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
     * @param IStateModel|null $model
     * @return Transition[]
     */
    public function getAvailableTransitions(?IStateModel $model): array {
        $state = $model ? $model->getState() : null;
        if (\is_null($state)) {
            $state = self::STATE_INIT;
        }
        return \array_filter($this->getTransitions(), function (Transition $transition) use ($model, $state): bool {
            return $transition->matchSource($state) && $this->canExecute($transition, $model);
        });
    }

    /**
     * @param string $id
     * @param IStateModel $model
     * @return Transition
     * @throws UnavailableTransitionsException
     */
    public function getAvailableTransitionById(string $id, IStateModel $model): Transition {
        $transitions = \array_filter($this->getAvailableTransitions($model), function (Transition $transition) use ($id): bool {
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

    public function setImplicitCondition(callable $implicitCondition): void {
        $this->implicitCondition = $implicitCondition;
    }

    protected function canExecute(Transition $transition, ?IStateModel $model): bool {
        if (isset($this->implicitCondition) && ($this->implicitCondition)($model)) {
            return true;
        }
        return $transition->canExecute2($model);
    }
    /* ********** EXECUTION ******** */

    /**
     * @param string $id
     * @param IStateModel $model
     * @return IStateModel
     * @throws ForbiddenRequestException
     * @throws UnavailableTransitionsException
     * @throws Exception
     */
    public function executeTransition(string $id, IStateModel $model): IStateModel {
        $transition = $this->getAvailableTransitionById($id, $model);
        if (!$this->canExecute($transition, $model)) {
            throw new ForbiddenRequestException(_('Prechod sa nedá vykonať'));
        }
        return $this->execute($transition, $model);
    }

    /**
     * @param Transition $transition
     * @param IStateModel|null $model
     * @return IStateModel
     * @throws BadTypeException
     * @throws Exception
     */
    private function execute(Transition $transition, IStateModel $model): IStateModel {
        if (!$this->context->getConnection()->getPdo()->inTransaction()) {
            $this->context->getConnection()->beginTransaction();
        }
        try {
            $transition->callBeforeExecute($model);
        } catch (Exception $exception) {
            $this->context->getConnection()->rollBack();
            throw $exception;
        }
        if (!$model instanceof IModel) {
            throw new BadTypeException(IModel::class, $model);
        }

        $this->context->getConnection()->commit();
        $this->service->updateModel2($model, [$model->getStateColumn() => $transition->getTargetState()]);
        /* select from DB new (updated) model */

        // $newModel = $model;
        /** @var IStateModel $newModel */
        $newModel = $model->refresh();
        $transition->callAfterExecute($newModel);
        return $newModel;
    }

    /* ********** MODEL CREATING ******** */

    abstract public function getCreatingState(): string;

    /**
     * @return Transition
     * @throws UnavailableTransitionsException
     */
    private function getCreatingTransition(): Transition {
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
     * @param IService $service
     * @return IStateModel
     *
     * @throws ForbiddenRequestException
     * @throws UnavailableTransitionsException
     * @throws Exception
     */
    public function createNewModel(array $data, IService $service): IStateModel {
        $transition = $this->getCreatingTransition();
        if (!$this->canExecute($transition, null)) {
            throw new ForbiddenRequestException(_('Model sa nedá vytvoriť'));
        }
        /** @var IStateModel|IModel|ActiveRow $model */
        $model = $service->createNewModel($data);
        return $this->execute($transition, $model);
    }
}
