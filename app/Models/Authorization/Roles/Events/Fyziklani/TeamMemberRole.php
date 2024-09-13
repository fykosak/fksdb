<?php

declare(strict_types=1);

namespace FKSDB\Models\Authorization\Roles\Events\Fyziklani;

use FKSDB\Models\Authorization\Roles\Events\EventRole;
use FKSDB\Models\Authorization\Roles\ImplicitRole;
use FKSDB\Models\ORM\Models\EventModel;
use FKSDB\Models\ORM\Models\Fyziklani\TeamMemberModel;
use Fykosak\NetteORM\Model\Model;
use Nette\Utils\Html;

final class TeamMemberRole implements EventRole, ImplicitRole
{
    public const RoleId = 'event.teamMember'; // phpcs:ignore
    private TeamMemberModel $member;

    public function __construct(TeamMemberModel $member)
    {
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

    public function getRoleId(): string
    {
        return self::RoleId;
    }

    public function getEvent(): EventModel
    {
        return $this->member->fyziklani_team->event;
    }

    public function getModel(): TeamMemberModel
    {
        return $this->member;
    }
}
