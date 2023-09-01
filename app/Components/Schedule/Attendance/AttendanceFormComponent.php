<?php

declare(strict_types=1);

namespace FKSDB\Components\Schedule\Attendance;

use FKSDB\Components\MachineCode\MachineCode;
use FKSDB\Components\MachineCode\FormComponent;
use FKSDB\Models\ORM\Models\PaymentState;
use FKSDB\Models\ORM\Models\PersonModel;
use FKSDB\Models\ORM\Models\Schedule\PersonScheduleModel;
use FKSDB\Models\ORM\Models\Schedule\PersonScheduleState;
use FKSDB\Models\ORM\Services\PersonService;
use FKSDB\Models\ORM\Services\Schedule\PersonScheduleService;
use FKSDB\Modules\Core\Language;
use Fykosak\NetteORM\Exceptions\ModelException;
use Fykosak\Utils\Logging\Message;
use Nette\Application\BadRequestException;
use Nette\Forms\Controls\SubmitButton;
use Nette\Forms\Form;

abstract class AttendanceFormComponent extends FormComponent
{
    private PersonService $personService;
    private PersonScheduleService $personScheduleService;

    public function inject(PersonService $personService, PersonScheduleService $personScheduleService): void
    {
        $this->personService = $personService;
        $this->personScheduleService = $personScheduleService;
    }

    /**
     * @throws \Exception
     */
    protected function innerHandleSuccess(MachineCode $code, Form $form): void
    {
        try {
            if ($code->type !== 'PE') {
                throw new BadRequestException(_('Bod code type'));
            }
            $person = $this->personService->findByPrimary($code->id);
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
            if ($personSchedule->state->value === PersonScheduleState::PARTICIPATED) { // TODO
                throw new BadRequestException(_('Already participated'));
            }
            /** @phpstan-var array{only_check:bool} $values */
            $values = $form->getValues('array');
            if ($values['only_check']) {
                $this->getPresenter()->flashMessage(
                    sprintf(
                        _('Person %s applied in %s.'),
                        $person->getFullName(),
                        $personSchedule->getLabel(Language::from($this->translator->lang))
                    ),
                    Message::LVL_INFO
                );
            } else {
                $this->personScheduleService->makeAttendance($personSchedule);
                $this->getPresenter()->flashMessage(
                    sprintf(
                        _('Person %s successfully showed up in %s.'),
                        $person->getFullName(),
                        $personSchedule->getLabel(Language::from($this->translator->lang))
                    ),
                    Message::LVL_SUCCESS
                );
            }
            $this->getPresenter()->redirect('this');
        } catch (BadRequestException | ModelException$exception) {
            $this->getPresenter()->flashMessage($exception->getMessage(), Message::LVL_ERROR);
            $this->getPresenter()->redirect('this');
        }
    }

    protected function appendSubmitButton(Form $form): SubmitButton
    {
        return $form->addSubmit('submit', _('Submit!'));
    }

    abstract protected function getPersonSchedule(PersonModel $person): ?PersonScheduleModel;

    protected function innerConfigureForm(Form $form): void
    {
        $form->addCheckbox('only_check', _('Only check state'));
    }
}
