<?php

declare(strict_types=1);

namespace FKSDB\Components\Schedule\Attendance;

use FKSDB\Components\Controls\Events\AttendanceCode;
use FKSDB\Components\Controls\FormComponent\FormComponent;
use FKSDB\Models\ORM\Models\PaymentState;
use FKSDB\Models\ORM\Models\PersonModel;
use FKSDB\Models\ORM\Models\Schedule\PersonScheduleModel;
use FKSDB\Models\ORM\Services\PersonService;
use FKSDB\Models\ORM\Services\Schedule\PersonScheduleService;
use Fykosak\NetteORM\Exceptions\ModelException;
use Fykosak\Utils\Logging\Message;
use Nette\Application\BadRequestException;
use Nette\Application\ForbiddenRequestException;
use Nette\Forms\Form;

abstract class AttendanceFormComponent extends FormComponent
{
    protected PersonService $personService;
    private PersonScheduleService $personScheduleService;

    public function inject(PersonService $personService, PersonScheduleService $personScheduleService): void
    {
        $this->personService = $personService;
        $this->personScheduleService = $personScheduleService;
    }

    /**
     * @throws \Exception
     */
    protected function handleSuccess(Form $form): void
    {
        try {
            $values = $form->getValues('array');
            $code = AttendanceCode::checkCode($this->container, $values['code']);
            $person = $this->personService->findByPrimary($code);
            if (!$person) {
                throw new BadRequestException(_('Person not found'));
            }
            $personSchedule = $this->getPersonSchedule($person);
            if (!$personSchedule) {
                throw new BadRequestException(_('Person not applied in this schedule'));
            }
            if (
                $personSchedule->schedule_item->isPayable() &&
                (!$personSchedule->getPayment() ||
                    $personSchedule->getPayment()->state->value !== PaymentState::RECEIVED)
            ) {
                throw new BadRequestException(_('Payment not found'));
            }
            if ($personSchedule->state === 'participated') {
                throw new BadRequestException(_('Already participated'));
            }
            if ($values['only_check']) {
                $this->getPresenter()->flashMessage(
                    sprintf(_('Person %s applied in %s.'), $person->getFullName(), $personSchedule->getLabel('cs')),
                    Message::LVL_INFO
                );
            } else {
                $this->personScheduleService->storeModel(['state' => 'participated'], $personSchedule);
                $this->getPresenter()->flashMessage(
                    sprintf(
                        _('Person %s successfully showed up in %s.'),
                        $person->getFullName(),
                        $personSchedule->getLabel('cs')
                    ),
                    Message::LVL_SUCCESS
                );
            }
            $this->getPresenter()->redirect('this');
        } catch (BadRequestException | ModelException | ForbiddenRequestException$exception) {
            $this->getPresenter()->flashMessage($exception->getMessage(), Message::LVL_ERROR);
            $this->getPresenter()->redirect('this');
        }
    }

    protected function appendSubmitButton(Form $form): void
    {
        $form->addSubmit('submit', _('Submit!'));
    }

    abstract protected function getPersonSchedule(PersonModel $person): ?PersonScheduleModel;

    protected function configureForm(Form $form): void
    {
        $form->addText('code', _('Code'));
        $form->addCheckbox('only_check', _('Only check state'));
    }
}
