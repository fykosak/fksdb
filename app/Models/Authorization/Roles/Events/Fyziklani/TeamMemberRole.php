<?php

declare(strict_types=1);

namespace FKSDB\Models\Authorization\Roles\Events\Fyziklani;

use FKSDB\Models\Authorization\Roles\Events\EventRole;
use FKSDB\Models\ORM\Models\EventModel;
use FKSDB\Models\ORM\Models\Fyziklani\TeamMemberModel;
use Nette\Utils\Html;

final class TeamMemberRole extends EventRole
{
    public const ROLE_ID = 'event.fyziklani.member';
    public TeamMemberModel $member;

    public function __construct(EventModel $event, TeamMemberModel $member)
    {
        parent::__construct(self::ROLE_ID, $event);
        $this->member = $member;
    }

    public function badge(): Html
    {
        return Html::el('span')
            ->addAttributes(['class' => 'badge bg-color-9'])
            ->addText(_('Member') . ': ')
            ->addHtml(
                Html::el('i')->addAttributes(
                    ['class' => $this->member->fyziklani_team->scholarship->getIconName() . ' me-1']
                )
            )
            ->addText(
                sprintf(
                    '%s (%s)',
                    $this->member->fyziklani_team->name,
                    $this->member->fyziklani_team->state->label()
                )
            );
    }
}
