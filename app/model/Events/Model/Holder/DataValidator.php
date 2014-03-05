<?php

namespace Events\Model\Holder;

use Nette\Object;

/**
 * Due to author's laziness there's no class doc (or it's self explaining).
 * 
 * @author Michal Koutný <michal@fykos.cz>
 */
class DataValidator extends Object {

    private $validationErrors;
    private $stateStack = array();

    public function validate(BaseHolder $baseHolder, $state) {
        $this->pushState($baseHolder, $state);

        // validate
        $this->validationErrors = array();
        $this->validateFields($baseHolder);

        $this->popState($baseHolder);
    }

    public function getValidationResult() {
        return count($this->validationErrors) ? $this->validationErrors : true;
    }

    private function validateFields(BaseHolder $baseHolder) {
        foreach ($baseHolder->getFields() as $field) {
            if (!$field->isSatisfied()) {
                $this->validationErrors[] = sprintf(_('Pole %s je povinné.'), $field->getLabel()); // TODO satisfiability != required (design more general mechanism)
            }
        }
    }

    private function pushState(BaseHolder $baseHolder, $state) {
        $baseMachine = $baseHolder->getHolder()->getMachine()->getBaseMachine($baseHolder->getName());

        $this->stateStack[] = $baseMachine->getState();
        $baseMachine->setState($state);
    }

    private function popState(BaseHolder $baseHolder) {
        $baseMachine = $baseHolder->getHolder()->getMachine()->getBaseMachine($baseHolder->getName());

        $state = array_pop($this->stateStack);
        $baseMachine->setState($state);
    }

}
