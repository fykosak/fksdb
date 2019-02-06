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
    private $stateStack = [];

    /**
     * @param BaseHolder $baseHolder
     * @param $state
     */
    public function validate(BaseHolder $baseHolder, $state) {
        $this->pushState($baseHolder, $state);

        // validate
        $this->validationErrors = [];
        $this->validateFields($baseHolder);

        $this->popState($baseHolder);
    }

    /**
     * @return bool
     */
    public function getValidationResult() {
        return count($this->validationErrors) ? $this->validationErrors : true;
    }

    /**
     * @param BaseHolder $baseHolder
     */
    private function validateFields(BaseHolder $baseHolder) {
        foreach ($baseHolder->getFields() as $field) {
            $field->validate($this);
        }
    }

    /**
     * @param $error
     */
    public function addError($error) {
        $this->validationErrors[] = $error;
    }

    /**
     * @param BaseHolder $baseHolder
     * @param $state
     */
    private function pushState(BaseHolder $baseHolder, $state) {
        $baseMachine = $baseHolder->getHolder()->getMachine()->getBaseMachine($baseHolder->getName());

        $this->stateStack[] = $baseMachine->getState();
        $baseMachine->setState($state);
    }

    /**
     * @param BaseHolder $baseHolder
     */
    private function popState(BaseHolder $baseHolder) {
        $baseMachine = $baseHolder->getHolder()->getMachine()->getBaseMachine($baseHolder->getName());

        $state = array_pop($this->stateStack);
        $baseMachine->setState($state);
    }

}
