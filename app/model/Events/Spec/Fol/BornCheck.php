<?php

namespace Events\Spec\Fol;

use Events\FormAdjustments\AbstractAdjustment;
use Events\FormAdjustments\IFormAdjustment;
use Events\Machine\Machine;
use Events\Model\Holder\Holder;
use Nette\Forms\Form;
use Nette\Forms\IControl;
use ServicePersonHistory;
use ServiceSchool;

/**
 * More user friendly Due to author's laziness there's no class doc (or it's self explaining).
 *
 * @author Michal Koutný <michal@fykos.cz>
 */
class BornCheck extends AbstractAdjustment implements IFormAdjustment {

    /**
     * @var ServiceSchool
     */
    private $serviceSchool;

    /**
     * @var ServicePersonHistory
     */
    private $servicePersonHistory;

    /**
     * @var Holder
     */
    private $holder;

    public function getHolder() {
        return $this->holder;
    }

    public function setHolder(Holder $holder) {
        $this->holder = $holder;
    }

    function __construct(ServiceSchool $serviceSchool, ServicePersonHistory $servicePersonHistory) {
        $this->serviceSchool = $serviceSchool;
        $this->servicePersonHistory = $servicePersonHistory;
    }

    protected function _adjust(Form $form, Machine $machine, Holder $holder) {
        $this->setHolder($holder);
        $schoolControls = $this->getControl('p*.person_id.person_history.school_id');
        $studyYearControls = $this->getControl("p*.person_id.person_history.study_year");
        $personControls = $this->getControl('p*.person_id');
        $bornControls = $this->getControl('p*.person_id.person_info.born');

        $msg = _('Datum narození je povinné.');

        foreach ($bornControls as $i => $control) {
            $schoolControl = $schoolControls[$i];
            $personControl = $personControls[$i];
            $studyYearControl = $studyYearControls[$i];
            $control->addCondition(~$form::FILLED)
                ->addRule(function (IControl $control) use ($schoolControl, $personControl, $studyYearControl, $form, $msg) {
                    if (!$personControl->getValue(false)) {
                        return true;
                    }
                    $schoolId = $this->getSchoolId($schoolControl, $personControl);
                    $studyYear = $this->getStudyYear($studyYearControl, $personControl);
                    if ($this->isCzSkSchool($schoolId) && $this->isStudent($studyYear)) {
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

    private function getStudyYear($studyYearControl, $personControl) {
        if ($studyYearControl->getValue()) {
            return $studyYearControl->getValue();
        }

        $personId = $personControl->getValue(false);
        $personHistory = $this->servicePersonHistory->getTable()
            ->where('person_id', $personId)
            ->where('ac_year', $this->getHolder()->getEvent()->getAcYear())->fetch();
        return $personHistory ? $personHistory->study_year : null;
    }

    private function getSchoolId($schoolControl, $personControl) {
        if ($schoolControl->getValue()) {
            return $schoolControl->getValue();
        }

        $personId = $personControl->getValue(false);
        $school = $this->servicePersonHistory->getTable()
            ->where('person_id', $personId)
            ->where('ac_year', $this->getHolder()->getEvent()->getAcYear())->fetch();
        return $school->school_id;
    }

    private function isCzSkSchool($school_id) {
        $country = $this->serviceSchool->getTable()->select('address.region.country_iso')->where(array('school_id' => $school_id))->fetch();
        if (in_array($country->country_iso, array('CZ', 'SK'))) {
            return true;
        }
        return false;
    }

    private function isStudent($study_year) {
        return ($study_year === null) ? false : true;
    }

}

