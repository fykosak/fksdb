<?php

namespace Events\Spec\Fol;

use Events\Machine\Machine;
use Events\Model\Holder\Holder;
use Events\Processings\AbstractProcessing;
use FKSDB\Logging\ILogger;
use FKSDB\ORM\Services\ServiceSchool;
use FKSDB\YearCalculator;
use Nette\Forms\Form;
use Nette\Utils\ArrayHash;

/**
 * Class FlagProcessing
 * @package Events\Spec\Fol
 */
class FlagProcessing extends AbstractProcessing {

    /**
     * @var \FKSDB\YearCalculator
     */
    private $yearCalculator;

    /**
     * @var ServiceSchool
     */
    private $serviceSchool;

    /**
     * FlagProcessing constructor.
     * @param \FKSDB\YearCalculator $yearCalculator
     * @param \FKSDB\ORM\Services\ServiceSchool $serviceSchool
     */
    function __construct(YearCalculator $yearCalculator, ServiceSchool $serviceSchool) {
        $this->yearCalculator = $yearCalculator;
        $this->serviceSchool = $serviceSchool;
    }

    /**
     * @param $states
     * @param ArrayHash $values
     * @param Machine $machine
     * @param Holder $holder
     * @param ILogger $logger
     * @param Form|null $form
     * @return mixed|void
     */
    protected function _process($states, ArrayHash $values, Machine $machine, Holder $holder, ILogger $logger, Form $form = null) {
        if (!isset($values['team'])) {
            return;
        }

        $event = $holder->getEvent();
        $contest = $event->getEventType()->contest;
        $year = $event->year;
        $acYear = $this->yearCalculator->getAcademicYear($contest, $year);

        foreach ($holder as $name => $baseHolder) {
            if ($name == 'team') {
                continue;
            }
            $formControls = [
                'school_id' => $this->getControl("$name.person_id.person_history.school_id"),
                'study_year' => $this->getControl("$name.person_id.person_history.study_year"),
            ];
            $formControls['school_id'] = reset($formControls['school_id']);
            $formControls['study_year'] = reset($formControls['study_year']);

            $formValues = [
                'school_id' => ($formControls['school_id'] ? $formControls['school_id']->getValue() : null),
                'study_year' => ($formControls['study_year'] ? $formControls['study_year']->getValue() : null),
            ];

            if (!$formValues['school_id']) {
                if ($this->isBaseReallyEmpty($name)) {
                    continue;
                }
                $person = $baseHolder->getModel()->getMainModel()->person;
                $history = $person->related('person_history')->where('ac_year', $acYear)->fetch();
                $participantData = [
                    'school_id' => $history->school_id,
                    'study_year' => $history->study_year,
                ];
            } else {
                $participantData = $formValues;
            }
            if (!($this->isCzSkSchool($participantData['school_id']) && $this->isStudent($participantData['study_year']))) {
                $personHasFlag = $values[$name]['person_id_1']['person_has_flag'];
                $personHasFlag->offsetUnset('spam_mff');
//                $a=$c;
//                $values[$name]['person_id_1']['person_has_flag']['spam_mff'] = null;
//                $a=$c;
                //unset($values[$name]['person_id_1']['person_has_flag']);
            }
        }
    }

    /**
     * @param $school_id
     * @return bool
     */
    private function isCzSkSchool($school_id) {
        $country = $this->serviceSchool->getTable()->select('address.region.country_iso')->where(['school_id' => $school_id])->fetch();
        if (in_array($country->country_iso, ['CZ', 'SK'])) {
            return true;
        }
        return false;
    }

    /**
     * @param $study_year
     * @return bool
     */
    private function isStudent($study_year) {
        return ($study_year === null) ? false : true;
    }
}
