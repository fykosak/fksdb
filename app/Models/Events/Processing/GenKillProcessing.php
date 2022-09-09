<?php

declare(strict_types=1);

namespace FKSDB\Models\Events\Processing;

use FKSDB\Models\Events\Exceptions\SubmitProcessingException;
use FKSDB\Models\Events\Machine\BaseMachine;
use FKSDB\Models\Events\Model\Holder\BaseHolder;
use FKSDB\Models\ORM\Models\EventParticipantStatus;
use FKSDB\Models\Transitions\Holder\ModelHolder;
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

    /**
     * @param BaseHolder $holder
     */
    public function process(
        ?EventParticipantStatus $state,
        ArrayHash $values,
        AbstractMachine $machine,
        ModelHolder $holder,
        Logger $logger,
        ?Form $form = null
    ): ?EventParticipantStatus {
        if (!isset($values[$holder->name])) { // whole machine unmodofiable/invisible
            return null;
        }
        if (!$holder->getDeterminingFields()) {
            // no way how to determine (non)existence of secondary models
            return null;
        }
        $isFilled = true;
        foreach ($holder->getDeterminingFields() as $field) {
            if (
                !isset($values[$holder->name][$field->name]) ||
                !$values[$holder->name][$field->name]
            ) {
                $isFilled = false;
                break;
            }
        }
        if (!$isFilled) {
            return EventParticipantStatus::tryFrom(AbstractMachine::STATE_TERMINATED);
        } elseif ($holder->getModelState() == AbstractMachine::STATE_INIT) {
            if (isset($values[$holder->name]['status'])) {
                return EventParticipantStatus::tryFrom($values[$holder->name]['status']);
            } else {
                $transitions = $machine->getAvailableTransitions(
                    $holder,
                    $holder->getModelState()
                );
                if (count($transitions) == 0) {
                    throw new SubmitProcessingException(
                        _("$holder->name: Není definován přechod z počátečního stavu.")
                    );
                } elseif (isset($state)) {
                    return $state; // propagate already set state
                } elseif (count($transitions) > 1) {
                    throw new SubmitProcessingException(
                        _("$holder->name: Přechod z počátečního stavu není jednoznačný.")
                    );
                } else {
                    return EventParticipantStatus::tryFrom(reset($transitions)->target);
                }
            }
        }
        return null;
    }
}
