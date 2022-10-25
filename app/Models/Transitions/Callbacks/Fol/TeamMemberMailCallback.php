<?php

declare(strict_types=1);

namespace FKSDB\Models\Transitions\Callbacks\Fol;

use FKSDB\Models\Transitions\Holder\ModelHolder;

class TeamMemberMailCallback extends \FKSDB\Models\Transitions\Callbacks\TeamMemberMailCallback
{
    protected function getTemplatePath(ModelHolder $holder): string
    {
        return __DIR__ . DIRECTORY_SEPARATOR . 'member';
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
