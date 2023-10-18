<?php

declare(strict_types=1);

namespace FKSDB\Components\Schedule\Code;

use FKSDB\Components\Controls\FormComponent\FormComponent;
use FKSDB\Components\MachineCode\MachineCode;
use FKSDB\Components\MachineCode\MachineCodeException;
use FKSDB\Models\ORM\Models\EventModel;
use FKSDB\Models\ORM\Models\EventParticipantModel;
use FKSDB\Models\ORM\Models\PersonModel;
use FKSDB\Models\ORM\Models\Schedule\PersonScheduleModel;
use Fykosak\NetteORM\Exceptions\ModelException;
use Fykosak\NetteORM\Model;
use Fykosak\Utils\Logging\Message;
use Nette\Application\BadRequestException;
use Nette\Forms\Controls\SubmitButton;
use Nette\Forms\Form;

abstract class CodeComponent extends FormComponent
{
    /**
     * @throws \Exception
     */
    protected function handleSuccess(Form $form): void
    {
        /** @phpstan-var array{code:string} $values
         */
        $values = $form->getValues('array');
        try {
            $personSchedule = $this->resolvePersonSchedule(
                MachineCode::parseHash(
                    $this->container,
                    $values['code'],
                    MachineCode::getSaltForEvent($this->getEvent())
                )
            );

            $this->getPresenter()->redirect(
                ':Schedule:Person:detail',
                ['id' => $personSchedule->person_schedule_id]
            );
            /* $this->getPresenter()->redirect(
                 $this->getEvent()->isTeamEvent()
                     ? ':Event:TeamApplication:detail'
                     : ':Event:Application:detail',
                 [
                     'id' => $personSchedule->person->getApplication(
                         $personSchedule->schedule_item->schedule_group->event
                     )->getPrimary(),
                 ]
             );*/
        } catch (BadRequestException | ModelException | MachineCodeException $exception) {
            $this->getPresenter()->flashMessage($exception->getMessage(), Message::LVL_ERROR);
            $this->getPresenter()->redirect('this');
        }
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

    protected function appendSubmitButton(Form $form): SubmitButton
    {
        return $form->addSubmit('submit', _('Submit!'));
    }

    protected function configureForm(Form $form): void
    {
        $form->addText('code', _('Code'));
    }

    abstract protected function getPersonSchedule(PersonModel $person): ?PersonScheduleModel;

    abstract protected function getEvent(): EventModel;
}
