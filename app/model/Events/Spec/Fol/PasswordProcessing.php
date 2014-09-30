<?php

namespace Events\Spec\Fol;

use Events\Machine\BaseMachine;
use Events\Machine\Machine;
use Events\Model\Holder\Holder;
use Events\Processings\AbstractProcessing;
use FKS\Logging\ILogger;
use Nette\ArrayHash;
use Nette\Forms\Form;

class PasswordProcessing extends AbstractProcessing {

    protected function _process($states, ArrayHash $values, Machine $machine, Holder $holder, ILogger $logger, Form $form = null) {
        if (!isset($values['team']) || !$values['team']['password']) {
            return;
        }

        $result = $values['team']['password'] = $this->hash($values['team']['password']);

        $original = $holder->getPrimaryHolder()->getModelState() != BaseMachine::STATE_INIT ? $holder->getPrimaryHolder()->getModel()->password : null;
        if ($original !== null && $original != $result) {
            $logger->log(_('Nastaveno nové herní heslo.'), ILogger::INFO);
        }
    }

    private function hash($string) {
        return sha1($string);
    }

}
