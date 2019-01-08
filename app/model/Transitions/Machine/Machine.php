<?php

namespace FKSDB\Transitions;

use Nette\Application\ForbiddenRequestException;
use Nette\Database\Connection;

/**
 * Due to author's laziness there's no class doc (or it's self explaining).
 *
 * @author Michal Koutný <michal@fykos.cz>
 */
class Machine {

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

    public function __construct(Connection $connection) {
        $this->connection = $connection;
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
        if (\is_null($state)) {
            $state = self::STATE_INIT;
        }
        return array_filter($this->transitions, function (Transition $transition) use ($model, $state) {
            return ($transition->getFromState() === $state) && $transition->canExecute($model);
        });
    }

    /**
     * @param $id
     * @param IStateModel $model
     * @return Transition
     * @throws UnavailableTransitionException
     */
    protected function findTransitionById(string $id, IStateModel $model): Transition {
        $matchedTransitions = \array_values(\array_filter($this->getAvailableTransitions($model), function (Transition $transition) use ($id) {
            return $transition->getId() === $id;
        }));

        /* if (\count($matchedTransitions) > 1) {
             // moc veľa
         }*/
        if (\count($matchedTransitions) === 1) {
            return $matchedTransitions[0];
        }
        throw new UnavailableTransitionException(\sprintf(_('Transition %s is not available'), $id));
    }

    /**
     * @param string $id
     * @param IStateModel $model
     * @return void
     * @throws ForbiddenRequestException
     * @throws UnavailableTransitionException
     */
    public function executeTransition(string $id, IStateModel $model) {
        $transition = $this->findTransitionById($id, $model);
        if (!$transition->canExecute($model)) {
            throw new ForbiddenRequestException(_('Prechod sa nedá vykonať'));
        }
        $this->connection->beginTransaction();
        try {
            $transition->beforeExecute($model);
        } catch (\Exception $exception) {
            $this->connection->rollBack();
            throw $exception;
        }

        $this->connection->commit();
        $model->updateState($transition->getToState());
        /* select from DB new (updated) model */
        $newModel = $model->refresh();
        $transition->afterExecute($newModel);
    }
}
