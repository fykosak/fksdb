<?php

declare(strict_types=1);

namespace FKSDB\Models\Transitions\Callbacks\Fof;

use FKSDB\Models\Transitions\Holder\FyziklaniTeamHolder;
use FKSDB\Models\Transitions\Holder\ModelHolder;

class TeamMemberMailCallback extends \FKSDB\Models\Transitions\Callbacks\TeamMemberMailCallback
{
    protected function getTemplatePath(ModelHolder $holder): string
    {
        return __DIR__ . DIRECTORY_SEPARATOR . 'member.latte';
    }

    /**
     * @param FyziklaniTeamHolder $holder
     */
    protected function getData(ModelHolder $holder): array
    {
        if ($holder->getModel()->game_lang->value === 'cs') {
            $subject = 'Registrace týmu na Fyziklání';
        } else {
            $subject = 'Fyziklani Team Registration';
        }
        return [
            'subject' => $subject,
            'blind_carbon_copy' => 'FYKOS <fyziklani@fykos.cz>',
            'sender' => 'Fyziklání <fyziklani@fykos.cz>',
        ];
    }
}
