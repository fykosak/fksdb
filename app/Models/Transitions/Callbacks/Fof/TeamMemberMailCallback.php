<?php

declare(strict_types=1);

namespace FKSDB\Models\Transitions\Callbacks\Fof;

use FKSDB\Models\Transitions\Holder\ModelHolder;

class TeamMemberMailCallback extends \FKSDB\Models\Transitions\Callbacks\TeamMemberMailCallback
{
    protected function getTemplatePath(ModelHolder $holder): string
    {
        return __DIR__ . DIRECTORY_SEPARATOR . 'member.latte';
    }

    protected function getData(ModelHolder $holder): array
    {
        return [
            'subject' => _('Fyziklani Team Registration'),
            'blind_carbon_copy' => 'FYKOS <fyziklani@fykos.cz>',
            'sender' => 'Fyziklání <fyziklani@fykos.cz>',
        ];
    }
}
