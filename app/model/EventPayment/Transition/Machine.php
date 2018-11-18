<?php

namespace FKSDB\EventPayment\Transition;

use FKSDB\EventPayment\PriceCalculator\PriceCalculator;
use FKSDB\EventPayment\SymbolGenerator\AbstractSymbolGenerator;
use FKSDB\ORM\ModelEventPayment;

/**
 * Due to author's laziness there's no class doc (or it's self explaining).
 *
 * @author Michal Koutný <michal@fykos.cz>
 */
class Machine {
    /**
     * @var PriceCalculator
     */
    // private $priceCalculator;
    /**
     * @var AbstractSymbolGenerator
     */
    // private $symbolGenerator;
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

    /**
     * @param string? $id
     * @param ModelEventPayment $model
     * @param boolean $isOrg
     * @return void
     * @throws UnavailableTransitionException
     */
    public function executeTransition($id, ModelEventPayment $model, bool $isOrg) {
        $availableTransitions = $this->getAvailableTransitions($model->state, $isOrg);
        foreach ($availableTransitions as $transition) {
            if ($transition->getId() === $id) {
                $transition->execute($model);
                $model->update(['state' => $transition->getToState()]);
                return;
            }
        }
        throw new UnavailableTransitionException(\sprintf(_('Transition %s is not available'), $id));
    }
/*
    public function getPriceCalculator(): PriceCalculator {
        return $this->priceCalculator;
    }

    public function getSymbolGenerator(): AbstractSymbolGenerator {
        return $this->symbolGenerator;
    }

    public function setPriceCalculator(PriceCalculator $priceCalculator) {
        $this->priceCalculator = $priceCalculator;
    }

    public function setSymbolGenerator(AbstractSymbolGenerator $abstractSymbolGenerator) {
        $this->symbolGenerator = $abstractSymbolGenerator;
    }*/
}
