<?php

namespace FKSDB\Transitions;

/**
 * Due to author's laziness there's no class doc (or it's self explaining).
 *
 * @author Michal Koutný <michal@fykos.cz>
 */
class Machine {

    /**
     * @var Transition[]
     */
    private $transitions = [];

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
        $state = $model ? $model->getState() : null;
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
    protected function findTransitionById($id, IStateModel $model): Transition {
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
     * @param string? $id
     * @param IStateModel $model
     * @return void
     * @throws UnavailableTransitionException
     * @throws \Nette\Application\ForbiddenRequestException
     */
    public function executeTransition($id, IStateModel $model) {
        $transition = $this->findTransitionById($id, $model);
        $transition->onExecute($model);
        $model->updateState($transition->getToState());
        $transition->onExecuted($model);
    }
}
