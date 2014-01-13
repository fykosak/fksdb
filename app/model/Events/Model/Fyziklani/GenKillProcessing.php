<?php

namespace Events\Model;

use Events\Machine\BaseMachine;
use Events\Machine\Machine;
use Events\SubmitProcessingException;
use Nette\ArrayHash;
use Nette\Object;

/**
 * Checks determining fields in sent data and either terminates the application
 * or tries to find unambiguous transition from the initial state.
 * 
 * @note Transition conditions are evaluated od pre-edited data.
 * @note All determining fields must be filled to consider application complete.
 * 
 * @author Michal Koutný <michal@fykos.cz>
 */
class GenKillProcessing extends Object implements IProcessing {

    public function process(ArrayHash $values, Machine $machine, Holder $holder) {
        $result = array();
        foreach ($holder as $name => $baseHolder) {
            if (!isset($values[$name])) { // whole machine unmodofiable/invisible
                continue;
            }
            $isFilled = true;
            foreach ($baseHolder->getDeterminingFields() as $field) {
                if (!$values[$name][$field->getName()]) {
                    $isFilled = false;
                    break;
                }
            }

            $baseMachine = $machine->getBaseMachine($name);
            if (!$isFilled) {
                $result[$name] = BaseMachine::STATE_TERMINATED;
            } elseif ($baseMachine->getState() == BaseMachine::STATE_INIT) {
                $transitions = $baseMachine->getAvailableTransitions();
                if (count($transitions) == 0) {
                    throw new SubmitProcessingException(_("$name: Není definován přechod z počátečního stavu."));
                } else if (count($transitions) > 1) {
                    throw new SubmitProcessingException(_("$name: Přechod z počátečního stavu není jednoznačný."));
                } else {
                    $result[$name] = reset($transitions)->getTarget();
                }
            }
        }
        return $result;
    }

}
