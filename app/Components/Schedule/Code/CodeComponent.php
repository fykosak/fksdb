<?php

declare(strict_types=1);

namespace FKSDB\Components\Schedule\Code;

use FKSDB\Components\Controls\FormComponent\CodeForm;
use FKSDB\Components\MachineCode\MachineCode;
use FKSDB\Components\MachineCode\MachineCodeException;
use FKSDB\Models\Exceptions\NotImplementedException;
use FKSDB\Models\ORM\Models\EventModel;
use FKSDB\Models\ORM\Models\EventParticipantModel;
use FKSDB\Models\ORM\Models\PersonModel;
use FKSDB\Models\ORM\Models\Schedule\PersonScheduleModel;
use Fykosak\NetteORM\Model;
use Nette\Application\BadRequestException;
use Nette\Forms\Form;

abstract class CodeComponent extends CodeForm
{
    /**
     * @throws BadRequestException
     * @throws MachineCodeException
     */
    protected function innerHandleSuccess(Model $model): void
    {
        $personSchedule = $this->resolvePersonSchedule($model);
        $this->getPresenter()->redirect(
            ':Schedule:Person:detail',
            ['id' => $personSchedule->person_schedule_id]
        );
    }

    protected function configureForm(Form $form): void
    {
        $form->elementPrototype->target = '_blank';
        parent::configureForm($form);
    }

    /**
     * @throws BadRequestException
     * @throws MachineCodeException
     */
    private function resolvePersonSchedule(Model $model): PersonScheduleModel
    {
        if ($model instanceof PersonModel) {
            $person = $model;
        } elseif ($model instanceof EventParticipantModel) {
            $person = $model->person;
        } else {
            throw new MachineCodeException(_('Unsupported code type'));
        }
        $personSchedule = $this->getPersonSchedule($person);

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
        return MachineCode::getSaltForEvent($this->getEvent());
    }

    abstract protected function getPersonSchedule(PersonModel $person): ?PersonScheduleModel;

    abstract protected function getEvent(): EventModel;
}
