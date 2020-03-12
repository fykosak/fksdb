<?php

namespace FKSDB\Transitions;

use Exception;
use FKSDB\Components\Controls\Transitions\TransitionButtonsControl;
use FKSDB\ORM\IModel;
use FKSDB\ORM\IService;
use LogicException;
use Nette\Application\BadRequestException;
use Nette\Application\ForbiddenRequestException;
use Nette\Database\Connection;
use Nette\Database\Table\ActiveRow;
use Nette\Localization\ITranslator;
use function array_filter;
use function array_values;
use function count;
use function is_null;

/**
 * Due to author's laziness there's no class doc (or it's self explaining).
 *
 * @author Michal Koutný <michal@fykos.cz>
 */
abstract class Machine {

    const STATE_INIT = '__init';
    const STATE_TERMINATED = '__terminated';

    /**
     * @var Transition[]
     */
    private $transitions = [];
    /**
     * @var Connection
     */
    protected $connection;
    /**
     * @var IService
     */
    private $service;
    /**
     * @var callable
     * if callback return true, transition is allowed explicit, independently of transition's condition
     */
    private $explicitCondition;
    /**
     * @var ITranslator
     */
    private $translator;

    /**
     * Machine constructor.
     * @param Connection $connection
     * @param IService $service
<<<<<<< HEAD
=======
     * @param ITranslator $translator
>>>>>>> origin/master
     */
    public function __construct(Connection $connection, IService $service, ITranslator $translator) {
        $this->connection = $connection;
        $this->service = $service;
        $this->translator = $translator;
    }

    /**
     * @param Transition $transition
     */
    public function addTransition(Transition $transition) {
        $this->transitions[] = $transition;
    }

    /**
     * @return Transition[]
     */
    public function getTransitions(): array {
        return $this->transitions;
    }

    /**
     * @param IStateModel $model
     * @return Transition[]
     */
    public function getAvailableTransitions(IStateModel $model = null): array {
        $state = $model ? $model->getState() : NULL;
        if (is_null($state)) {
            $state = self::STATE_INIT;
        }
        return array_filter($this->getTransitions(), function (Transition $transition) use ($model, $state) {
            return ($transition->getFromState() === $state) && $this->canExecute($transition, $model);
        });
    }

    /**
     * @param IStateModel $model
     * @return TransitionButtonsControl
     */
    public function createComponentTransitionButtons(IStateModel $model): TransitionButtonsControl {
        return new TransitionButtonsControl($this, $this->translator, $model);
    }

    /**
     * @param string $id
     * @param IStateModel $model
     * @return Transition
     * @throws UnavailableTransitionException
     * @throws Exception
     */
    protected function findTransitionById(string $id, IStateModel $model): Transition {
        $transitions = array_filter($this->getAvailableTransitions($model), function (Transition $transition) use ($id) {
            return $transition->getId() === $id;
        });

        return $this->selectTransition($transitions);
    }

    /**
     * @param array $transitions
     * @return Transition
     * @throws UnavailableTransitionException
     * @throws LogicException
     * @throws UnavailableTransitionException
     * Protect more that one transition between nodes
     */
    private function selectTransition(array $transitions): Transition {
        $length = count($transitions);
        if ($length > 1) {
            throw new UnavailableTransitionException();
        }
        if (!$length) {
            throw new UnavailableTransitionException();
        }
        return array_values($transitions)[0];
    }

    /* ********** CONDITION ******** */
    /**
     * @param callable $condition
     */
    public function setExplicitCondition(callable $condition) {
        $this->explicitCondition = $condition;
    }

    /**
     * @param Transition $transition
     * @param IStateModel|null $model
     * @return bool
     */
    protected function canExecute(Transition $transition, IStateModel $model = null): bool {
        if ($this->explicitCondition && ($this->explicitCondition)($model)) {
            return true;
        }
        return $transition->canExecute($model);
    }
    /* ********** EXECUTION ******** */

    /**
     * @param string $id
     * @param IStateModel $model
     * @return IStateModel
     * @throws UnavailableTransitionException
     * @throws ForbiddenRequestException
     * @throws BadRequestException
     * @throws Exception
     */
    public function executeTransition(string $id, IStateModel $model): IStateModel {
        $transition = $this->findTransitionById($id, $model);
        if (!$this->canExecute($transition, $model)) {
            throw new ForbiddenRequestException(_('Prechod sa nedá vykonať'));
        }
        return $this->execute($transition, $model);
    }

    /**
     * @param Transition $transition
     * @param IStateModel|null $model
     * @return IStateModel
     * @throws BadRequestException
     * @throws Exception
     */
    private function execute(Transition $transition, IStateModel $model = null): IStateModel {
        if (!$this->connection->getPdo()->inTransaction()) {
            $this->connection->beginTransaction();
        }
        try {
            $transition->beforeExecute($model);
        } catch (Exception $exception) {
            $this->connection->rollBack();
            throw $exception;
        }
        if (!$model instanceof IModel) {
            throw new BadRequestException(_('Expected instance of IModel'));
        }

        $this->connection->commit();
        $model->updateState($transition->getToState());
        /* select from DB new (updated) model */

        // $newModel = $model;
        $newModel = $model->refresh();
        $transition->afterExecute($newModel);
        return $newModel;
    }

    /* ********** MODEL CREATING ******** */

    /**
     * @return string
     */
    abstract public function getCreatingState(): string;

    /**
     * @return Transition
     * @throws Exception
     */
    private function getCreatingTransition(): Transition {
        $transitions = array_filter($this->getTransitions(), function (Transition $transition) {
            return $transition->getFromState() === self::STATE_INIT && $transition->getToState() === $this->getCreatingState();
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
     * @param $data
     * @param IService $service
     * @return IStateModel
     * @throws ForbiddenRequestException
     * @throws Exception
     */
    public function createNewModel($data, IService $service): IStateModel {
        $transition = $this->getCreatingTransition();
        if (!$this->canExecute($transition, null)) {
            throw new ForbiddenRequestException(_('Model sa nedá vytvoriť'));
        }
        /**
         * @var IStateModel|IModel|ActiveRow $model
         */
        $model = $service->createNewModel($data);
        return $this->execute($transition, $model);
    }
}
