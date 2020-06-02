<?php

namespace FKSDB\Events\Spec\Fol;

use FKSDB\Events\Machine\BaseMachine;
use FKSDB\Events\Machine\Machine;
use FKSDB\Events\Model\Holder\Holder;
use FKSDB\Events\Processings\AbstractProcessing;
use FKSDB\Logging\ILogger;
use FKSDB\Messages\Message;
use Nette\Forms\Form;
use Nette\Utils\ArrayHash;

/**
 * Class PasswordProcessing
 * *
 */
class PasswordProcessing extends AbstractProcessing {

    protected function _process(array $states, ArrayHash $values, Machine $machine, Holder $holder, ILogger $logger, ?Form $form): void {
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
            $logger->log(new Message(_('Nastaveno nové herní heslo.'), ILogger::INFO));
        }
    }

    /**
     * @param $string
     * @return string
     */
    private function hash($string) {
        return sha1($string);
    }

}
