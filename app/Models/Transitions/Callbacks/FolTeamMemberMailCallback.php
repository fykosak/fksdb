<?php

declare(strict_types=1);

namespace FKSDB\Models\Transitions\Callbacks;

use FKSDB\Models\Transitions\Holder\ModelHolder;

class FolTeamMemberMailCallback extends TeamMemberMailCallback
{
    protected function getTemplatePath(ModelHolder $holder): string
    {
        return 'fol/member';
    }

    protected function getData(ModelHolder $holder): array
    {
        return [
            'subject' => _('Physics Brawl Online Team Registration'),
            'blind_carbon_copy' => 'Fyziklání Online <online@fyziklani.cz>',
            'sender' => _('Physics Brawl Online <online@physicsbrawl.org>'),
        ];
    }
}
