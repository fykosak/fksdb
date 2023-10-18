<?php

declare(strict_types=1);

namespace FKSDB\Components\Schedule\Code;

use FKSDB\Components\Controls\FormComponent\FormComponent;
use FKSDB\Components\MachineCode\MachineCode;
use FKSDB\Components\MachineCode\MachineCodeException;
use FKSDB\Models\ORM\Models\EventModel;
use FKSDB\Models\ORM\Models\EventParticipantModel;
use FKSDB\Models\ORM\Models\PaymentState;
use FKSDB\Models\ORM\Models\PersonModel;
use FKSDB\Models\ORM\Models\Schedule\PersonScheduleModel;
use FKSDB\Models\ORM\Services\Schedule\PersonScheduleService;
use FKSDB\Modules\Core\Language;
use Fykosak\NetteORM\Exceptions\ModelException;
use Fykosak\NetteORM\Model;
use Fykosak\Utils\Logging\Message;
use Nette\Application\BadRequestException;
use Nette\Forms\Controls\SubmitButton;
use Nette\Forms\Form;

abstract class CodeComponent extends FormComponent
{
    private PersonScheduleService $personScheduleService;

    public function inject(PersonScheduleService $personScheduleService): void
    {
        $this->personScheduleService = $personScheduleService;
    }

    /**
     * @throws \Exception
     */
    protected function handleSuccess(Form $form): void
    {
        /** @phpstan-var array{code:string,action:string} $values */
        $values = $form->getValues('array');
        try {
            $personSchedule = $this->resolvePersonSchedule(
                MachineCode::parseHash(
                    $this->container,
                    $values['code'],
                    MachineCode::getSaltForEvent($this->getEvent())
                )
            );

            switch ($values['action']) {
                case 'attendance':
                    $this->checkPayment($personSchedule);
                    $this->personScheduleService->makeAttendance($personSchedule);
                    $this->getPresenter()->flashMessage(
                        sprintf(
                            _('Person %s successfully showed up in %s.'),
                            $personSchedule->person->getFullName(),
                            $personSchedule->getLabel(Language::from($this->translator->lang))
                        ),
                        Message::LVL_SUCCESS
                    );
                    break;
                case 'check':
                    $this->checkPayment($personSchedule);
                    $this->getPresenter()->flashMessage(
                        sprintf(
                            _('Person %s applied in %s.'),
                            $personSchedule->person->getFullName(),
                            $personSchedule->getLabel(Language::from($this->translator->lang))
                        ),
                        Message::LVL_INFO
                    );
                    break;
                case 'detail':
                    $this->getPresenter()->redirect(':Schedule:Person:detail', ['id' => $personSchedule->person_id]);
                    break;// @phpstan-ignore-line
                case 'application':
                    $this->getPresenter()->redirect(
                        $this->getEvent()->isTeamEvent()
                            ? ':Event:TeamApplication:detail'
                            : ':Event:Application:detail',
                        [
                            'id' => $personSchedule->person->getApplication(
                                $personSchedule->schedule_item->schedule_group->event
                            )->getPrimary(),
                        ]
                    );
                    break;// @phpstan-ignore-line
            }
            $this->getPresenter()->redirect('this');
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

    /**
     * @throws BadRequestException
     * @throws \Exception
     */
    private function checkPayment(PersonScheduleModel $personSchedule): void
    {
        if (
            $personSchedule->schedule_item->isPayable() &&
            (!$personSchedule->getPayment() ||
                $personSchedule->getPayment()->state->value !== PaymentState::RECEIVED)
        ) {
            throw new BadRequestException(_('Payment not found'));
        }
    }

    protected function appendSubmitButton(Form $form): SubmitButton
    {
        return $form->addSubmit('submit', _('Submit!'));
    }

    abstract protected function getPersonSchedule(PersonModel $person): ?PersonScheduleModel;

    abstract protected function getEvent(): EventModel;

    protected function configureForm(Form $form): void
    {
        $form->addText('code', _('Code'));
        $form->addSelect('action', _('Action'), [
            'attendance' => _('Attendance'),
            'check' => _('Check'),
            'detail' => _('button.detail'),
            'application' => _('Show application!'),
        ]);
    }
}
