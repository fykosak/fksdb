<?php

namespace FKSDB\Events\Spec\Fol;

use FKSDB\ORM\Models\ModelPersonHistory;
use FKSDB\ORM\Models\ModelSchool;
use FKSDB\Events\FormAdjustments\AbstractAdjustment;
use FKSDB\Events\FormAdjustments\IFormAdjustment;
use FKSDB\Events\Machine\Machine;
use FKSDB\Events\Model\Holder\Holder;
use FKSDB\ORM\Services\ServicePersonHistory;
use FKSDB\ORM\Services\ServiceSchool;
use Nette\Forms\Controls\BaseControl;
use Nette\Forms\Form;
use Nette\Forms\IControl;

/**
 * More user friendly Due to author's laziness there's no class doc (or it's self explaining).
 *
 * @author Michal Koutný <michal@fykos.cz>
 */
class BornCheck extends AbstractAdjustment implements IFormAdjustment {

    private ServiceSchool $serviceSchool;

    private ServicePersonHistory $servicePersonHistory;

    /**
     * @var Holder
     */
    private $holder;

    /**
     * @return Holder
     */
    public function getHolder() {
        return $this->holder;
    }

    public function setHolder(Holder $holder): void {
        $this->holder = $holder;
    }

    /**
     * BornCheck constructor.
     * @param ServiceSchool $serviceSchool
     * @param ServicePersonHistory $servicePersonHistory
     */
    public function __construct(ServiceSchool $serviceSchool, ServicePersonHistory $servicePersonHistory) {
        $this->serviceSchool = $serviceSchool;
        $this->servicePersonHistory = $servicePersonHistory;
    }

    protected function conform(Form $form, Machine $machine, Holder $holder): void {
        $this->setHolder($holder);
        $schoolControls = $this->getControl('p*.person_id.person_history.school_id');
        $studyYearControls = $this->getControl("p*.person_id.person_history.study_year");
        $personControls = $this->getControl('p*.person_id');
        $bornControls = $this->getControl('p*.person_id.person_info.born');

        $msg = _('Datum narození je povinné.');
        /** @var BaseControl $control */
        foreach ($bornControls as $i => $control) {
            $schoolControl = $schoolControls[$i];
            $personControl = $personControls[$i];
            $studyYearControl = $studyYearControls[$i];
            $control->addCondition(~$form::FILLED)
                ->addRule(function (IControl $control) use ($schoolControl, $personControl, $studyYearControl, $form, $msg) {
                    if (!$personControl->getValue()) {
                        return true;
                    }
                    $schoolId = $this->getSchoolId($schoolControl, $personControl);
                    $studyYear = $this->getStudyYear($studyYearControl, $personControl);
                    if ($this->serviceSchool->isCzSkSchool($schoolId) && $this->isStudent($studyYear)) {
                        $form->addError($msg);
                        return false;
                    }
                    return true;
                }, $msg);
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

    /**
     * @param IControl $studyYearControl
     * @param IControl $personControl
     * @return int|null
     */
    private function getStudyYear($studyYearControl, $personControl) {
        if ($studyYearControl->getValue()) {
            return $studyYearControl->getValue();
        }

        $personId = $personControl->getValue();
        /** @var ModelPersonHistory|false $personHistory */
        $personHistory = $this->servicePersonHistory->getTable()
            ->where('person_id', $personId)
            ->where('ac_year', $this->getHolder()->getPrimaryHolder()->getEvent()->getAcYear())->fetch();
        return $personHistory ? $personHistory->study_year : null;
    }

    /**
     * @param IControl $schoolControl
     * @param IControl $personControl
     * @return int
     */
    private function getSchoolId($schoolControl, $personControl) {
        if ($schoolControl->getValue()) {
            return $schoolControl->getValue();
        }
        $personId = $personControl->getValue();
        /** @var ModelSchool|false $school */
        $school = $this->servicePersonHistory->getTable()
            ->where('person_id', $personId)
            ->where('ac_year', $this->getHolder()->getPrimaryHolder()->getEvent()->getAcYear())->fetch();
        return $school->school_id;
    }

    /**
     * @param mixed $studyYear
     * @return bool
     */
    private function isStudent($studyYear): bool {
        return ($studyYear === null) ? false : true;
    }
}
