<?php

declare(strict_types=1);

namespace FKSDB\Components\Schedule\Attendance;

use FKSDB\Components\Transitions\Code\CodeTransition;
use FKSDB\Models\Events\EventDispatchFactory;
use FKSDB\Models\MachineCode\MachineCodeException;
use FKSDB\Models\ORM\Models\PersonModel;
use FKSDB\Models\ORM\Models\Schedule\PersonScheduleModel;
use FKSDB\Models\ORM\Models\Schedule\PersonScheduleState;
use FKSDB\Models\ORM\Models\Schedule\ScheduleItemModel;
use FKSDB\Models\Transitions\Machine\Machine;
use FKSDB\Models\Transitions\Machine\PersonScheduleMachine;
use Fykosak\NetteORM\Model\Model;
use Fykosak\Utils\Logging\Message;
use Nette\Application\BadRequestException;
use Nette\DI\Container;
use Nette\Forms\Form;

class CodeAttendance extends CodeTransition
{
    protected ScheduleItemModel $item;
    private EventDispatchFactory $eventDispatchFactory;

    public function __construct(Container $container, ScheduleItemModel $item, PersonScheduleMachine $machine)
    {
        parent::__construct($container, PersonScheduleState::from(PersonScheduleState::Participated), $machine);
        $this->item = $item;
    }

    public function inject(EventDispatchFactory $eventDispatchFactory): void
    {
        $this->eventDispatchFactory = $eventDispatchFactory;
    }

    protected function innerHandleSuccess(Model $model, Form $form): void
    {
        try {
            $machine = $this->eventDispatchFactory->getPersonScheduleMachine();
            $personSchedule = $this->resolvePersonSchedule($model);
            $holder = $machine->createHolder($personSchedule);
            $transition = Machine::selectTransition(
                Machine::filterByTarget(
                    Machine::filterAvailable($machine->transitions, $holder),
                    PersonScheduleState::from(PersonScheduleState::Participated)
                )
            );
            $machine->execute($transition, $holder);
        } catch (\Throwable $exception) {
            $this->flashMessage(_('Error') . ': ' . $exception->getMessage(), Message::LVL_ERROR);
            $this->getPresenter()->redirect('this');
        }

        $this->flashMessage(
            sprintf(_('Transition successful for: %s'), $personSchedule->person->getFullName()),
            Message::LVL_SUCCESS
        );
        $this->getPresenter()->redirect('this');
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
}
