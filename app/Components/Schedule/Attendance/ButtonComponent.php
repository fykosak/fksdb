<?php

declare(strict_types=1);

namespace FKSDB\Components\Schedule\Attendance;

use FKSDB\Models\Events\EventDispatchFactory;
use FKSDB\Models\ORM\Models\Schedule\PersonScheduleModel;
use FKSDB\Models\ORM\Models\Schedule\PersonScheduleState;
use FKSDB\Models\Transitions\Machine\Machine;
use Fykosak\Utils\BaseComponent\BaseComponent;
use Fykosak\Utils\Logging\Message;
use Nette\DI\Container;

class ButtonComponent extends BaseComponent
{
    private PersonScheduleModel $model;
    private EventDispatchFactory $eventDispatchFactory;

    public function __construct(Container $container, PersonScheduleModel $model)
    {
        parent::__construct($container);
        $this->model = $model;
    }

    public function inject(EventDispatchFactory $eventDispatchFactory): void
    {
        $this->eventDispatchFactory = $eventDispatchFactory;
    }

    public function render(): void
    {
        $this->template->model = $this->model;
        $this->template->render(__DIR__ . DIRECTORY_SEPARATOR . 'layout.latte');
    }

    public function handleAttendance(): void
    {
        try {
            $this->model->checkPayment();
            $machine = $this->eventDispatchFactory->getPersonScheduleMachine();
            $holder = $machine->createHolder($this->model);
            $transition = Machine::selectTransition(
                Machine::filterByTarget(
                    Machine::filterAvailable($machine->transitions, $holder),
                    PersonScheduleState::from(PersonScheduleState::Participated)
                )
            );
            $machine->execute($transition, $holder);
        } catch (\Throwable $exception) {
            $this->flashMessage(_('Error: ') . $exception->getMessage(), Message::LVL_ERROR);
            $this->getPresenter()->redirect('this');
        }
        $this->flashMessage(
            sprintf(_('Transition successful for: %s'), $this->model->person->getFullName()),
            Message::LVL_SUCCESS
        );
        $this->getPresenter()->redirect('this');
    }
}
