<?php

declare(strict_types=1);

namespace FKSDB\Models\Events\Spec\Fol;

use FKSDB\Models\Events\Processing\AbstractProcessing;
use FKSDB\Models\Transitions\Holder\ModelHolder;
use Fykosak\Utils\Logging\Logger;
use Fykosak\Utils\Logging\Message;
use Nette\Utils\ArrayHash;

class PasswordProcessing extends AbstractProcessing
{
    protected function innerProcess(
        ArrayHash $values,
        ModelHolder $holder,
        Logger $logger
    ): void {
        if (!isset($values['team'])) {
            return;
        }
        $model = $holder->getModel();
        $original = $model ? $model->password : ($holder->data['password'] ?? null);

        if (isset($values['team']['password']) && $values['team']['password']) {
            $result = $values['team']['password'] = $this->hash($values['team']['password']);
        } else {
            $result = $values['team']['password'] = $original;
        }

        if ($original !== null && $original != $result) {
            $logger->log(new Message(_('Set new game password.'), Message::LVL_INFO));
        }
    }

    private function hash(?string $string): string
    {
        return sha1($string);
    }
}
