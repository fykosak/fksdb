<?php

declare(strict_types=1);

namespace FKSDB\Models\Transitions\Callbacks\Fol;

use FKSDB\Models\Transitions\Holder\ModelHolder;
use FKSDB\Models\Transitions\Holder\TeamHolder;
use FKSDB\Models\Transitions\Transition\Transition;

class TeamMemberMailCallback extends \FKSDB\Models\Transitions\Callbacks\TeamMemberMailCallback
{
    /**
     * @param TeamHolder $holder
     * @phpstan-param Transition<TeamHolder> $transition
     */
    protected function getTemplatePath(ModelHolder $holder, Transition $transition): string
    {
        return __DIR__ . DIRECTORY_SEPARATOR . 'member';
    }

    /**
     * @param TeamHolder $holder
     * @phpstan-param Transition<TeamHolder> $transition
     * @phpstan-return array{
     *     blind_carbon_copy:string|null,
     *     subject:string,
     *     sender:string,
     * }
     */
    protected function getData(ModelHolder $holder, Transition $transition): array
    {
        return [
            'subject' => _('Physics Brawl Online Team Registration'),
            'blind_carbon_copy' => 'Fyziklání Online <online@fyziklani.cz>',
            'sender' => _('Physics Brawl Online <online@physicsbrawl.org>'),
        ];
    }
}
