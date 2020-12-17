<?php

namespace FKSDB\Model\Events\Spec\Fol;

use FKSDB\Model\Events\Machine\Machine;
use FKSDB\Model\Events\Model\Holder\Holder;
use FKSDB\Model\Events\Processing\AbstractProcessing;
use FKSDB\Model\Logging\ILogger;
use FKSDB\Model\Messages\Message;
use Nette\Forms\Form;
use Nette\Utils\ArrayHash;

/**
 * Class PasswordProcessing
 * *
 */
class PasswordProcessing extends AbstractProcessing {

    protected function innerProcess(array $states, ArrayHash $values, Machine $machine, Holder $holder, ILogger $logger, ?Form $form): void {
        if (!isset($values['team'])) {
            return;
        }

        $original = $holder->getPrimaryHolder()->getModelState() != \FKSDB\Model\Transitions\Machine\Machine::STATE_INIT ? $holder->getPrimaryHolder()->getModel()->password : null;

        if (!isset($values['team']['password']) || !$values['team']['password']) {
            $result = $values['team']['password'] = $original;
        } else {
            $result = $values['team']['password'] = $this->hash($values['team']['password']);
        }

        if ($original !== null && $original != $result) {
            $logger->log(new Message(_('Set new game password.'), ILogger::INFO));
        }
    }

    private function hash(?string $string): string {
        return sha1($string);
    }
}