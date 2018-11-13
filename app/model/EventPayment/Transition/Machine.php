<?php

namespace FKSDB\EventPayment\Transition;

use FKSDB\ORM\ModelEventPayment;

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
     * @param string? $state
     * @param boolean $isOrg
     * @return Transition[]
     */
    public function getAvailableTransitions($state, $isOrg): array {
        return array_filter($this->transitions, function (Transition $transition) use ($state, $isOrg) {
            return ($transition->getFromState() === $state) && $transition->canExecute($isOrg);
        });
    }

    public function executeTransition($id, ModelEventPayment $model, $isOrg) {
        $availableTransitions = $this->getAvailableTransitions($model->state, $isOrg);
        foreach ($availableTransitions as $transition) {
            if ($transition->getId() === $id) {
                $transition->execute($model);
                return $transition->getToState();
            }
        }
        throw new UnavailableTransitionException(\sprintf(_('Transition %s is not available'), $id));
    }
}
