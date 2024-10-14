<?php

declare(strict_types=1);

namespace FKSDB\Models\ORM\Models\Fyziklani;

use FKSDB\Models\Authorization\Roles\EventRole;
use FKSDB\Models\MachineCode\MachineCode;
use FKSDB\Models\ORM\Models\EventModel;
use FKSDB\Models\ORM\Models\PersonModel;
use Fykosak\NetteORM\Model\Model;
use Nette\Utils\Html;

/**
 * @property-read int $fyziklani_team_teacher_id
 * @property-read int $person_id
 * @property-read PersonModel $person
 * @property-read int $fyziklani_team_id
 * @property-read TeamModel2 $fyziklani_team
 */
final class TeamTeacherModel extends Model implements EventRole
{
    public const RoleId = 'event.teamTeacher';// phpcs:ignore

    public function createMachineCode(): ?string
    {
        try {
            return MachineCode::createModelHash($this->person, $this->fyziklani_team->event->getSalt());
        } catch (\Throwable $exception) {
            return null;
        }
    }

    public function badge(): Html
    {
        return Html::el('span')
            ->addAttributes(['class' => 'badge bg-color-9'])
            ->addText(_('Teacher') . ': ')
            ->addHtml(
                Html::el('i')->addAttributes(
                    ['class' => $this->fyziklani_team->scholarship->getIconName() . ' me-1']
                )
            )
            ->addText(
                sprintf(
                    '%s (%s)',
                    $this->fyziklani_team->name,
                    $this->fyziklani_team->state->label()
                )
            );
    }

    public function getRoleId(): string
    {
        return self::RoleId;
    }

    public function getEvent(): EventModel
    {
        return $this->fyziklani_team->event;
    }
}
