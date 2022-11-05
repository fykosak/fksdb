<?php

declare(strict_types=1);

namespace FKSDB\Models\Events\Spec\Fol;

use FKSDB\Models\Events\FormAdjustments\AbstractAdjustment;
use FKSDB\Models\Events\Model\Holder\BaseHolder;
use FKSDB\Models\ORM\Models\PersonHistoryModel;
use FKSDB\Models\ORM\Models\SchoolModel;
use FKSDB\Models\ORM\Services\PersonHistoryService;
use FKSDB\Models\ORM\Services\SchoolService;
use FKSDB\Models\Transitions\Holder\ModelHolder;
use Nette\Forms\Controls\BaseControl;
use Nette\Forms\Form;
use Nette\Forms\Control;

class FlagCheck extends AbstractAdjustment
{

    private SchoolService $schoolService;
    private PersonHistoryService $personHistoryService;
    protected BaseHolder $holder;

    public function __construct(SchoolService $schoolService, PersonHistoryService $personHistoryService)
    {
        $this->schoolService = $schoolService;
        $this->personHistoryService = $personHistoryService;
    }

    /**
     * @param BaseHolder $holder
     */
    protected function innerAdjust(Form $form, ModelHolder $holder): void
    {
        $this->holder = $holder;
        $schoolControls = $this->getControl('p*.person_id.person_history.school_id');
        $studyYearControls = $this->getControl('p*.person_id.person_history.study_year');
        $personControls = $this->getControl('p*.person_id');
        $spamControls = $this->getControl('p*.person_id.person_has_flag.spam_mff');

        $msgForeign = _('This option is only available to Czech and Slovak students.');
        $msgOld = _('This option is only available to secondary school students.');
        /**
         * @var  $i
         * @var BaseControl $control
         */
        foreach ($spamControls as $i => $control) {
            $schoolControl = $schoolControls[$i];
            $personControl = $personControls[$i];
            $studyYearControl = $studyYearControls[$i];
            $control->addCondition($form::FILLED)
                ->addRule(function () use ($schoolControl, $personControl, $form, $msgForeign): bool {
                    $schoolId = $this->getSchoolId($schoolControl, $personControl);
                    if (!$this->schoolService->isCzSkSchool($schoolId)) {
                        $form->addError($msgForeign);
                        return false;
                    }
                    return true;
                }, $msgForeign)
                ->addRule(function () use ($studyYearControl, $personControl, $form, $msgOld): bool {
                    $studyYear = $this->getStudyYear($studyYearControl, $personControl);
                    if (!$this->isStudent($studyYear)) {
                        $form->addError($msgOld);
                        return false;
                    }
                    return true;
                }, $msgOld);
        }
//        $form->onValidate[] = function(Form $form) use($schoolControls, $spamControls, $studyYearControls, $message) {
//                    if ($form->isValid()) { // it means that all schools may have been disabled
//                        foreach ($spamControls as $i => $control) {
//                            $schoolId = $schoolControls[$i]->getValue();
//                            $studyYear = $studyYearControls[$i]->getValue();
//                            if ($control->isFilled)
//                            if (!($this->isCzSkSchool($schoolId) && $this->isStudent($studyYear))) {
//                                $form->addError($message);
//                            }
//                        }
//                    }
//                };
    }

    private function getStudyYear(Control $studyYearControl, Control $personControl): ?int
    {
        if ($studyYearControl->getValue()) {
            return $studyYearControl->getValue();
        }

        $personId = $personControl->getValue();
        /** @var PersonHistoryModel $personHistory */
        $personHistory = $this->personHistoryService->getTable()
            ->where('person_id', $personId)
            ->where('ac_year', $this->holder->event->getContestYear()->ac_year)
            ->fetch();
        return $personHistory->study_year;
    }

    private function getSchoolId(Control $schoolControl, Control $personControl): ?int
    {
        if ($schoolControl->getValue()) {
            return (int)$schoolControl->getValue();
        }

        $personId = $personControl->getValue();
        /** @var SchoolModel|null $school */
        $school = $this->personHistoryService->getTable()
            ->where('person_id', $personId)
            ->where('ac_year', $this->holder->event->getContestYear()->ac_year)->fetch();
        return $school->school_id;
    }

    private function isStudent(?int $studyYear): bool
    {
        return !is_null($studyYear);
    }
}
