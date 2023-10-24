<?php

declare(strict_types=1);

namespace FKSDB\Components\Schedule\Attendance;

use FKSDB\Components\Controls\FormComponent\CodeForm;
use FKSDB\Components\MachineCode\MachineCode;
use FKSDB\Components\MachineCode\MachineCodeException;
use FKSDB\Models\Exceptions\NotImplementedException;
use FKSDB\Models\ORM\Models\EventParticipantModel;
use FKSDB\Models\ORM\Models\PersonModel;
use FKSDB\Models\ORM\Models\Schedule\PersonScheduleModel;
use FKSDB\Models\ORM\Models\Schedule\ScheduleItemModel;
use FKSDB\Models\ORM\Services\Schedule\PersonScheduleService;
use Fykosak\NetteORM\Model;
use Fykosak\Utils\Logging\Message;
use Nette\Application\BadRequestException;
use Nette\DI\Container;
use Nette\Forms\Form;

class CodeComponent extends CodeForm
{
    protected ScheduleItemModel $item;
    private PersonScheduleService $service;

    public function __construct(Container $container, ScheduleItemModel $item)
    {
        parent::__construct($container);
        $this->item = $item;
    }

    public function inject(PersonScheduleService $service): void
    {
        $this->service = $service;
    }

    protected function innerHandleSuccess(Model $model, Form $form): void
    {
        try {
            $personSchedule = $this->resolvePersonSchedule($model);
            $this->service->makeAttendance($personSchedule);
        } catch (\Throwable $exception) {
            $this->flashMessage(_('error: ') . $exception->getMessage(), Message::LVL_ERROR);
            $this->getPresenter()->redirect('this');
        }

        $this->flashMessage(
            sprintf(_('Transition successful for %s'), $personSchedule->person->getFullName()),
            Message::LVL_SUCCESS
        );
        $this->getPresenter()->redirect('this');
    }

    /**
     * @throws BadRequestException
     * @throws MachineCodeException
     */
    protected function resolvePersonSchedule(Model $model): PersonScheduleModel
    {
        if ($model instanceof PersonModel) {
            $person = $model;
        } elseif ($model instanceof EventParticipantModel) {
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
     * @throws NotImplementedException
     */
    protected function getSalt(): string
    {
        return MachineCode::getSaltForEvent($this->item->schedule_group->event);
    }
}
