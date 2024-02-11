<?php

declare(strict_types=1);

namespace FKSDB\Modules\EventModule\Schedule;

use FKSDB\Components\Controls\FormControl\FormControl;
use FKSDB\Models\Exceptions\NotFoundException;
use FKSDB\Models\ORM\Models\Schedule\PersonScheduleModel;
use FKSDB\Models\ORM\Models\Schedule\ScheduleGroupModel;
use FKSDB\Models\ORM\Services\Schedule\PersonScheduleService;
use FKSDB\Models\Utils\FormUtils;
use Fykosak\Utils\Logging\Message;
use Fykosak\Utils\UI\PageTitle;
use Nette\Forms\Form;

final class FeedbackPresenter extends BasePresenter
{
    private PersonScheduleService $personScheduleService;
    /** @persistent */
    public int $id;

    final public function injectService(
        PersonScheduleService $personScheduleService
    ): void {
        $this->personScheduleService = $personScheduleService;
    }

    /**
     * @throws NotFoundException
     */
    public function authorizedDefault(): bool
    {
        return true;
    }

    public function titleDefault(): PageTitle
    {
        return new PageTitle(null, _('Feedback'));
    }

    /**
     * @return FormControl
     * @throws NotFoundException
     */
    protected function createComponentForm(): FormControl
    {
        $control = new FormControl($this->getContext());
        $form = $control->getForm();
        $form->addTextArea('feedback');
        $form->addSubmit('submit', _('Save feedback'));
        $form->onSuccess[] = function (Form $form): void {
            /** @phpstan-var array{feedback:string} $values */
            $values = $form->getValues('array');
            $values = FormUtils::emptyStrToNull2($values);
            $this->personScheduleService->storeModel(['feedback' => $values['feedback']], $this->getModel());
            $this->flashMessage(_('Feedback saved'), Message::LVL_SUCCESS);
        };
        $form->setDefaults(['feedback' => $this->getModel()->feedback]);
        return $control;
    }


    /**
     * @return PersonScheduleModel
     * @throws NotFoundException
     */
    private function getModel(): PersonScheduleModel
    {
        $person = $this->getLoggedPerson();
        /** @var ScheduleGroupModel|null $group */
        $group = $this->getEvent()->getScheduleGroups()->where('schedule_group_id', $this->id)->fetch();
        if (!$group) {
            throw new NotFoundException();
        }
        $personSchedule = $person->getScheduleByGroup($group);
        if (!$personSchedule) {
            throw new NotFoundException();
        }
        return $personSchedule;
    }
}
