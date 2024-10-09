<?php

declare(strict_types=1);

namespace FKSDB\Models\Authorization\Roles\Events;

use FKSDB\Models\Authorization\Roles\ImplicitRole;
use FKSDB\Models\ORM\Models\EventModel;
use FKSDB\Models\ORM\Models\Schedule\PersonScheduleModel;
use Nette\Utils\Html;

final class ScheduleParticipant implements EventRole, ImplicitRole
{
    public const RoleId = 'event.scheduleParticipant'; // phpcs:ignore

    private PersonScheduleModel $personSchedule;

    public function __construct(PersonScheduleModel $personSchedule)
    {
        $this->personSchedule = $personSchedule;
    }

    public function getEvent(): EventModel
    {
        return $this->personSchedule->schedule_item->schedule_group->event;
    }

    public function getModel(): PersonScheduleModel
    {
        return $this->personSchedule;
    }

    public function badge(): Html
    {
        return Html::el('span')
            ->addAttributes(['class' => 'badge bg-color-10'])
            ->addText(_('Schedule participant'));
    }

    public function getRoleId(): string
    {
        return self::RoleId;
    }
}
