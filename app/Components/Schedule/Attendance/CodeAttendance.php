<?php

declare(strict_types=1);

namespace FKSDB\Components\Schedule\Attendance;

use FKSDB\Components\Transitions\Code\CodeTransition;
use FKSDB\Models\MachineCode\MachineCodeException;
use FKSDB\Models\ORM\Models\PersonModel;
use FKSDB\Models\ORM\Models\Schedule\PersonScheduleModel;
use FKSDB\Models\ORM\Models\Schedule\PersonScheduleState;
use FKSDB\Models\ORM\Models\Schedule\ScheduleItemModel;
use FKSDB\Models\Transitions\Machine\PersonScheduleMachine;
use Fykosak\NetteORM\Model\Model;
use Nette\Application\BadRequestException;
use Nette\DI\Container;

/**
 * @phpstan-extends CodeTransition<PersonScheduleModel>
 */
final class CodeAttendance extends CodeTransition
{
    protected ScheduleItemModel $item;

    public function __construct(Container $container, ScheduleItemModel $item, PersonScheduleMachine $machine)
    {
        /** @phpstan-ignore-next-line */
        parent::__construct($container, PersonScheduleState::from(PersonScheduleState::Participated), $machine);
        $this->item = $item;
    }

    /**
     * @throws BadRequestException
     * @throws MachineCodeException
     */
    protected function resolveModel(Model $model): PersonScheduleModel
    {
        if (!$model instanceof PersonModel) {
            throw new MachineCodeException(_('Unsupported code type'));
        }
        $personSchedule = $model->getScheduleByItem($this->item);

        if (!$personSchedule) {
            throw new BadRequestException(_('Person not applied in this schedule'));
        }
        return $personSchedule;
    }

    /**
     * @throws MachineCodeException
     */
    protected function getSalt(): string
    {
        return $this->item->schedule_group->event->getSalt();
    }

    protected function finalRedirect(): void
    {
    }
}
