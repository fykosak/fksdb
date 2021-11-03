<?php

namespace FKSDB\Models\Events\Spec\Fol;

use FKSDB\Models\Events\Model\Holder\Holder;
use FKSDB\Models\Events\Processing\AbstractProcessing;
use Fykosak\Utils\Logging\Logger;
use Fykosak\Utils\Logging\Message;
use Nette\Forms\Form;
use Nette\Utils\ArrayHash;

class PasswordProcessing extends AbstractProcessing {

    protected function innerProcess(array $states, ArrayHash $values, Holder $holder, Logger $logger, ?Form $form): void {
        if (!isset($values['team'])) {
            return;
        }
        $model = $holder->getPrimaryHolder()->getModel2();
        $original = $model ? $model->password : ($holder->getPrimaryHolder()->data['password'] ?? null);

        if (isset($values['team']['password']) && $values['team']['password']) {
            $result = $values['team']['password'] = $this->hash($values['team']['password']);
        } else {
            $result = $values['team']['password'] = $original;
        }

        if ($original !== null && $original != $result) {
            $logger->log(new Message(_('Set new game password.'), Message::LVL_INFO));
        }
    }

    private function hash(?string $string): string {
        return sha1($string);
    }
}
