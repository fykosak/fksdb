<?php

declare(strict_types=1);

namespace FKSDB\Components\Schedule\Attendance;

use FKSDB\Components\Transitions\Code\CodeTransition;
use FKSDB\Components\Controls\FormComponent\CodeForm;
use FKSDB\Models\Events\EventDispatchFactory;
use FKSDB\Models\MachineCode\MachineCodeException;
use FKSDB\Models\ORM\Models\EventParticipantModel;
use FKSDB\Models\ORM\Models\Fyziklani\TeamMemberModel;
use FKSDB\Models\ORM\Models\Fyziklani\TeamTeacherModel;
use FKSDB\Models\ORM\Models\Schedule\PersonScheduleModel;
use FKSDB\Models\ORM\Models\Schedule\PersonScheduleState;
use FKSDB\Models\ORM\Models\Schedule\ScheduleItemModel;
use FKSDB\Models\Transitions\Machine\PersonScheduleMachine;
use FKSDB\Models\Transitions\Machine\Machine;
use Fykosak\NetteORM\Model\Model;
use Fykosak\Utils\Logging\Message;
use Nette\Application\BadRequestException;
use Nette\DI\Container;

class CodeComponent extends CodeTransition
{
    protected ScheduleItemModel $item;

    public function __construct(Container $container, ScheduleItemModel $item, PersonScheduleMachine $machine)
    {
        parent::__construct($container, PersonScheduleState::from(PersonScheduleState::Participated), $machine);
        $this->item = $item;
    }

    /**
     * @throws BadRequestException
     * @throws MachineCodeException
     */
    protected function resolveModel(Model $model): PersonScheduleModel
    {
        if (
            $model instanceof TeamTeacherModel
            || $model instanceof TeamMemberModel
            || $model instanceof EventParticipantModel
        ) {
            $person = $model->person;
        } else {
            throw new MachineCodeException(_('Unsupported code type'));
        }
        $personSchedule = $person->getScheduleByItem($this->item);

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
}
