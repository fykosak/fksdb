<?php

namespace FKSDB\Models\Events\Spec\Fol;

use FKSDB\Models\Events\Machine\BaseMachine;
use FKSDB\Models\Events\Machine\Machine;
use FKSDB\Models\Events\Model\Holder\Holder;
use FKSDB\Models\Events\Processing\AbstractProcessing;
use FKSDB\Models\Logging\Logger;
use FKSDB\Models\Messages\Message;
use Nette\Forms\Form;
use Nette\Utils\ArrayHash;

class PasswordProcessing extends AbstractProcessing {

    protected function innerProcess(array $states, ArrayHash $values, Machine $machine, Holder $holder, Logger $logger, ?Form $form): void {
        if (!isset($values['team'])) {
            return;
        }

        $original = $holder->getPrimaryHolder()->getModelState() != BaseMachine::STATE_INIT ? $holder->getPrimaryHolder()->getModel()->password : null;

        if (!isset($values['team']['password']) || !$values['team']['password']) {
            $result = $values['team']['password'] = $original;
        } else {
            $result = $values['team']['password'] = $this->hash($values['team']['password']);
        }

        if ($original !== null && $original != $result) {
            $logger->log(new Message(_('Set new game password.'), Logger::INFO));
        }
    }

    private function hash(?string $string): string {
        return sha1($string);
    }
}
