<?php

declare(strict_types=1);

namespace FKSDB\Models\Transitions\Callbacks\Fof;

use FKSDB\Models\Transitions\Holder\ModelHolder;
use FKSDB\Models\Transitions\Holder\TeamHolder;
use Fykosak\NetteORM\Model;

class TeamMemberMailCallback extends \FKSDB\Models\Transitions\Callbacks\TeamMemberMailCallback
{
    protected function getTemplatePath(ModelHolder $holder): string
    {
        return __DIR__ . DIRECTORY_SEPARATOR . 'member.latte';
    }

    /**
     * @param TeamHolder $holder
     */
    protected function getData(ModelHolder $holder): array
    {
        if ($holder->getModel()->game_lang->value === 'cs') {
            $subject = 'Registrace na Fyziklání – ' . $holder->getModel()->name;
            $sender = 'Fyziklání <fyziklani@fykos.cz>';
        } else {
            $subject = 'Fyziklani Registration – ' . $holder->getModel()->name;
            $sender = 'Fyziklani <fyziklani@fykos.cz>';
        }
        return [
            'subject' => $subject,
            'blind_carbon_copy' => 'FYKOS <fyziklani@fykos.cz>',
            'sender' => $sender,
        ];
    }
}
