<?php

declare(strict_types=1);

namespace FKSDB\Models\Events\Processing;

use FKSDB\Models\Events\Exceptions\SubmitProcessingException;
use FKSDB\Models\Events\Machine\Machine;
use FKSDB\Models\Events\Model\Holder\BaseHolder;
use FKSDB\Models\Events\Model\Holder\Holder;
use FKSDB\Models\Transitions\Machine\AbstractMachine;
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
class GenKillProcessing implements Processing
{
    use SmartObject;

    public function process(
        array $states,
        ArrayHash $values,
        Machine $machine,
        Holder $holder,
        Logger $logger,
        ?Form $form = null
    ): array {
        $result = [];

        if (!isset($values[$holder->primaryHolder->name])) { // whole machine unmodofiable/invisible
            return $result;
        }
        if (!$holder->primaryHolder->getDeterminingFields()) {
            // no way how to determine (non)existence of secondary models
            return $result;
        }
        $isFilled = true;
        foreach ($holder->primaryHolder->getDeterminingFields() as $field) {
            if (
                !isset($values[$holder->primaryHolder->name][$field->getName()]) ||
                !$values[$holder->primaryHolder->name][$field->getName()]
            ) {
                $isFilled = false;
                break;
            }
        }
        $baseMachine = $machine->primaryMachine;
        if (!$isFilled) {
            $result[$holder->primaryHolder->name] = AbstractMachine::STATE_TERMINATED;
        } elseif ($holder->primaryHolder->getModelState() == AbstractMachine::STATE_INIT) {
            if (isset($values[$holder->primaryHolder->name][BaseHolder::STATE_COLUMN])) {
                $result[$holder->primaryHolder->name] =
                    $values[$holder->primaryHolder->name][BaseHolder::STATE_COLUMN];
            } else {
                $transitions = $baseMachine->getAvailableTransitions(
                    $holder,
                    $holder->primaryHolder->getModelState()
                );
                if (count($transitions) == 0) {
                    throw new SubmitProcessingException(
                        _("$holder->primaryHolder->name: Není definován přechod z počátečního stavu.")
                    );
                } elseif (isset($states[$holder->primaryHolder->name])) {
                    $result[$holder->primaryHolder->name] = $states[$holder->primaryHolder->name]; // propagate already set state
                } elseif (count($transitions) > 1) {
                    throw new SubmitProcessingException(
                        _("$holder->primaryHolder->name: Přechod z počátečního stavu není jednoznačný.")
                    );
                } else {
                    $result[$holder->primaryHolder->name] = reset($transitions)->target;
                }
            }

        }
        return $result;
    }
}
