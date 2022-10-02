<?php

declare(strict_types=1);

namespace FKSDB\Models\Transitions\Callbacks;

use FKSDB\Models\Transitions\Holder\ModelHolder;

class FofTeamMemberMailCallback extends TeamMemberMailCallback
{
    protected function getTemplatePath(ModelHolder $holder): string
    {
        return 'fof/member';
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
