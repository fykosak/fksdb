<?php

namespace FKSDB\Events\Spec\Fol;

use FKSDB\ORM\Models\ModelSchool;
use FKSDB\Events\FormAdjustments\AbstractAdjustment;
use FKSDB\Events\FormAdjustments\IFormAdjustment;
use FKSDB\Events\Machine\Machine;
use FKSDB\Events\Model\Holder\Holder;
use FKSDB\ORM\Services\ServicePersonHistory;
use FKSDB\ORM\Services\ServiceSchool;
use Nette\Database\Table\ActiveRow;
use Nette\Database\Table\Selection;
use Nette\Forms\Controls\BaseControl;
use Nette\Forms\Form;
use Nette\Forms\IControl;

/**
 * More user friendly Due to author's laziness there's no class doc (or it's self explaining).
 *
 * @author Michal Koutný <michal@fykos.cz>
 */
class FlagCheck extends AbstractAdjustment implements IFormAdjustment {

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

    /**
     * @return Holder
     */
    public function getHolder() {
        return $this->holder;
    }

    /**
     * @param Holder $holder
     * @return void
     */
    public function setHolder(Holder $holder) {
        $this->holder = $holder;
    }

    /**
     * FlagCheck constructor.
     * @param ServiceSchool $serviceSchool
     * @param ServicePersonHistory $servicePersonHistory
     */
    public function __construct(ServiceSchool $serviceSchool, ServicePersonHistory $servicePersonHistory) {
        $this->serviceSchool = $serviceSchool;
        $this->servicePersonHistory = $servicePersonHistory;
    }

    /**
     * @param Form $form
     * @param Machine $machine
     * @param Holder $holder
     * @return void
     */
    protected function _adjust(Form $form, Machine $machine, Holder $holder) {
        $this->setHolder($holder);
        $schoolControls = $this->getControl('p*.person_id.person_history.school_id');
        $studyYearControls = $this->getControl("p*.person_id.person_history.study_year");
        $personControls = $this->getControl('p*.person_id');
        $spamControls = $this->getControl('p*.person_id.person_has_flag.spam_mff');

        $msgForeign = _('Zasílání informačních materiálů je dostupné pouze českým a slovenským studentům.');
        $msgOld = _('Zasílání informačních materiálů je dostupné pouze SŠ studentům.');
        /**
         * @var  $i
         * @var BaseControl $control
         */
        foreach ($spamControls as $i => $control) {
            $schoolControl = $schoolControls[$i];
            $personControl = $personControls[$i];
            $studyYearControl = $studyYearControls[$i];
            $control->addCondition($form::FILLED)
                ->addRule(function (IControl $control) use ($schoolControl, $personControl, $form, $msgForeign) {
                    $schoolId = $this->getSchoolId($schoolControl, $personControl);
                    if (!$this->serviceSchool->isCzSkSchool($schoolId)) {
                        $form->addError($msgForeign);
                        return false;
                    }
                    return true;
                }, $msgForeign)
                ->addRule(function (IControl $control) use ($studyYearControl, $personControl, $form, $msgOld) {
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

    /**
     * @param $studyYearControl
     * @param $personControl
     * @return bool|mixed|ActiveRow|Selection|null
     */
    private function getStudyYear($studyYearControl, $personControl) {
        if ($studyYearControl->getValue()) {
            return $studyYearControl->getValue();
        }

        $personId = $personControl->getValue(false);
        $personHistory = $this->servicePersonHistory->getTable()
            ->where('person_id', $personId)
            ->where('ac_year', $this->getHolder()->getPrimaryHolder()->getEvent()->getAcYear())->fetch();
        return $personHistory->study_year;
    }

    /**
     * @param $schoolControl
     * @param $personControl
     * @return bool|mixed|ActiveRow|Selection|null
     */
    private function getSchoolId($schoolControl, $personControl) {
        if ($schoolControl->getValue()) {
            return $schoolControl->getValue();
        }

        $personId = $personControl->getValue(false);
        /** @var ModelSchool|false $school */
        $school = $this->servicePersonHistory->getTable()
            ->where('person_id', $personId)
            ->where('ac_year', $this->getHolder()->getPrimaryHolder()->getEvent()->getAcYear())->fetch();
        return $school->school_id;
    }

    /**
     * @param $studyYear
     * @return bool
     */
    private function isStudent($studyYear) {
        return ($studyYear === null) ? false : true;
    }
}
