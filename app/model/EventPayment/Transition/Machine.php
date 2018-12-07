<?php

namespace FKSDB\EventPayment\Transition;

use FKSDB\EventPayment\PriceCalculator\PriceCalculator;
use FKSDB\EventPayment\SymbolGenerator\AbstractSymbolGenerator;

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
    public function getAvailableTransitions($model): array {
        return array_filter($this->transitions, function (Transition $transition) use ($model) {
            return ($transition->getFromState() === ($model ? $model->getState() : null)) && $transition->canExecute($model);
        });
    }

    /**
     * @param string? $id
     * @param IStateModel $model
     * @return void
     * @throws UnavailableTransitionException
     * @throws \Nette\Application\ForbiddenRequestException
     */
    public function executeTransition($id, IStateModel $model) {
        $availableTransitions = $this->getAvailableTransitions($model);
        foreach ($availableTransitions as $transition) {
            if ($transition->getId() === $id) {
                $transition->onExecute($model);
                $model->updateState($transition->getToState());
                $transition->onExecuted($model);
                return;
            }
        }
        throw new UnavailableTransitionException(\sprintf(_('Transition %s is not available'), $id));
    }
}
