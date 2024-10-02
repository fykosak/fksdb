<?php

declare(strict_types=1);

namespace FKSDB\Models\Authorization\Roles\Events\Fyziklani;

use FKSDB\Models\Authorization\Roles\Events\EventRole;
use FKSDB\Models\Authorization\Roles\ImplicitRole;
use FKSDB\Models\ORM\Models\EventModel;
use FKSDB\Models\ORM\Models\Fyziklani\TeamTeacherModel;
use Nette\Utils\Html;

final class TeamTeacherRole implements EventRole, ImplicitRole
{
    public const RoleId = 'event.teamTeacher'; // phpcs:ignore

    private TeamTeacherModel $teacher;

    public function __construct(TeamTeacherModel $teacher)
    {
        $this->teacher = $teacher;
    }

    public function badge(): Html
    {
        return Html::el('span')
            ->addAttributes(['class' => 'badge bg-color-9'])
            ->addText(_('Teacher') . ': ')
            ->addHtml(
                Html::el('i')->addAttributes(
                    ['class' => $this->teacher->fyziklani_team->scholarship->getIconName() . ' me-1']
                )
            )
            ->addText(
                sprintf(
                    '%s (%s)',
                    $this->teacher->fyziklani_team->name,
                    $this->teacher->fyziklani_team->state->label()
                )
            );
    }

    public function getRoleId(): string
    {
        return self::RoleId;
    }

    public function getEvent(): EventModel
    {
        return $this->teacher->fyziklani_team->event;
    }

    public function getModel(): TeamTeacherModel
    {
        return $this->teacher;
    }
}
