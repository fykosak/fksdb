<?php

namespace Events\Processings;

use Events\Machine\BaseMachine;
use Events\Machine\Machine;
use Events\Model\Holder\BaseHolder;
use Events\Model\Holder\Holder;
use Events\SubmitProcessingException;
use FKSDB\Logging\ILogger;
use Nette\Utils\ArrayHash;
use Nette\Forms\Form;
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

    public function process($states, ArrayHash $values, Machine $machine, Holder $holder, ILogger $logger, Form $form = null) {
        $result = [];
        foreach ($holder as $name => $baseHolder) {
            if (!isset($values[$name])) { // whole machine unmodofiable/invisible
                continue;
            }
            if (!$baseHolder->getDeterminingFields()) { // no way how to determine (non)existence of secondary models
                continue;
            }
            $isFilled = true;
            foreach ($baseHolder->getDeterminingFields() as $field) {
                if (!isset($values[$name][$field->getName()]) || !$values[$name][$field->getName()]) {
                    $isFilled = false;
                    break;
                }
            }

            $baseMachine = $machine->getBaseMachine($name);
            if (!$isFilled) {
                $result[$name] = BaseMachine::STATE_TERMINATED;
            } elseif ($baseMachine->getState() == BaseMachine::STATE_INIT) {
                if (isset($values[$name][BaseHolder::STATE_COLUMN])) {
                    $result[$name] = $values[$name][BaseHolder::STATE_COLUMN];
                } else {
                    $transitions = $baseMachine->getAvailableTransitions();
                    if (count($transitions) == 0) {
                        throw new SubmitProcessingException(_("$name: Není definován přechod z počátečního stavu."));
                    } else if (isset($states[$name])) {
                        $result[$name] = $states[$name]; // propagate already set state
                    } else if (count($transitions) > 1) {
                        throw new SubmitProcessingException(_("$name: Přechod z počátečního stavu není jednoznačný."));
                    } else {
                        $result[$name] = reset($transitions)->getTarget();
                    }
                }
            }
        }
        return $result;
    }

}
