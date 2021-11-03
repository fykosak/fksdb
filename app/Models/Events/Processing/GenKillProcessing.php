<?php

namespace FKSDB\Models\Events\Processing;

use FKSDB\Models\Events\Exceptions\SubmitProcessingException;
use FKSDB\Models\Events\Machine\Machine;
use FKSDB\Models\Events\Model\Holder\BaseHolder;
use FKSDB\Models\Events\Model\Holder\Holder;
use Fykosak\Utils\Logging\Logger;
use Nette\Forms\Form;
use Nette\SmartObject;
use Nette\Utils\ArrayHash;

/**
 * Checks determining fields in sent data and either terminates the application
 * or tries to find unambiguous transition from the initial state.
 *
 * @note Transition conditions are evaluated od pre-edited data.
 * @note All determining fields must be filled to consider application complete.
 */
class GenKillProcessing implements Processing {

    use SmartObject;

    public function process(array $states, ArrayHash $values, Machine $machine, Holder $holder, Logger $logger, ?Form $form = null): array {
        $result = [];
        foreach ($holder->getBaseHolders() as $name => $baseHolder) {
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
                $result[$name] = \FKSDB\Models\Transitions\Machine\Machine::STATE_TERMINATED;
            } elseif ($holder->getBaseHolder($name)->getModelState() == \FKSDB\Models\Transitions\Machine\Machine::STATE_INIT) {
                if (isset($values[$name][BaseHolder::STATE_COLUMN])) {
                    $result[$name] = $values[$name][BaseHolder::STATE_COLUMN];
                } else {
                    $transitions = $baseMachine->getAvailableTransitions($holder, $holder->getBaseHolder($name)->getModelState());
                    if (count($transitions) == 0) {
                        throw new SubmitProcessingException(_("$name: Není definován přechod z počátečního stavu."));
                    } elseif (isset($states[$name])) {
                        $result[$name] = $states[$name]; // propagate already set state
                    } elseif (count($transitions) > 1) {
                        throw new SubmitProcessingException(_("$name: Přechod z počátečního stavu není jednoznačný."));
                    } else {
                        $result[$name] = reset($transitions)->getTargetState();
                    }
                }
            }
        }
        return $result;
    }
}
