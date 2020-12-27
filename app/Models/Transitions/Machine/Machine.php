<?php

namespace FKSDB\Models\Transitions\Machine;

use Exception;

use FKSDB\Models\Exceptions\BadTypeException;
use FKSDB\Models\ORM\IModel;
use FKSDB\Models\ORM\IService;
use FKSDB\Models\ORM\Models\AbstractModelSingle;
use FKSDB\Models\Transitions\Holder\IModelHolder;
use FKSDB\Models\Transitions\Transition\Transition;
use FKSDB\Models\Transitions\Transition\UnavailableTransitionsException;
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
    /**
     * @var callable|null
     * if callback return true, transition is allowed explicit, independently of transition's condition
     */
    private $implicitCondition = null;

    public function __construct(Context $context) {
        $this->context = $context;
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
     * @param IModelHolder|null $model
     * @return Transition[]
     */
    public function getAvailableTransitions(?IModelHolder $model): array {
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
     * @param IModelHolder $model
     * @return Transition
     * @throws UnavailableTransitionsException
     */
    public function getAvailableTransitionById(string $id, IModelHolder $model): Transition {
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

    protected function canExecute(Transition $transition, ?IModelHolder $model): bool {
        if (isset($this->implicitCondition) && ($this->implicitCondition)($model)) {
            return true;
        }
        return $transition->canExecute2($model);
    }
    /* ********** EXECUTION ******** */

    /**
     * @param string $id
     * @param IModelHolder $model
     * @return IModelHolder
     * @throws ForbiddenRequestException
     * @throws UnavailableTransitionsException
     * @throws Exception
     */
    public function executeTransition(string $id, IModelHolder $model): IModelHolder {
        $transition = $this->getAvailableTransitionById($id, $model);
        if (!$this->canExecute($transition, $model)) {
            throw new ForbiddenRequestException(_('Prechod sa nedá vykonať'));
        }
        return $this->execute($transition, $model);
    }

    /**
     * @param Transition $transition
     * @param IModelHolder|null $model
     * @return IModelHolder
     * @throws BadTypeException
     * @throws Exception
     */
    private function execute(Transition $transition, IModelHolder $model): IModelHolder {
        if (!$this->context->getConnection()->getPdo()->inTransaction()) {
            $this->context->getConnection()->beginTransaction();
        }
        try {
            $transition->callBeforeExecute($model);
        } catch (Exception $exception) {
            $this->context->getConnection()->rollBack();
            throw $exception;
        }
        if (!$model instanceof IModelHolder) {
            throw new BadTypeException(IModelHolder::class, $model);
        }
        $this->context->getConnection()->commit();
        $newModel = $model->updateState($transition->getTargetState());
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
        return $this->canExecute($this->getCreatingTransition());
    }

    /**
     * @param array $data
     * @param IService $service
     * @return IModelHolder
     * @throws ForbiddenRequestException
     * @throws UnavailableTransitionsException
     * @throws Exception
     */
    public function createNewModel(array $data, IService $service): IModelHolder {
        $transition = $this->getCreatingTransition();
        if (!$this->canExecute($transition, null)) {
            throw new ForbiddenRequestException(_('Model sa nedá vytvoriť'));
        }
        /** @var IModelHolder $model */
        $model = $service->createNewModel($data);
        return $this->execute($transition, $model);
    }

    abstract public function createHolder(AbstractModelSingle $model): IModelHolder;
}
