<?php

namespace Events\Spec\Fol;

use Events\Machine\Machine;
use Events\Model\Holder\Holder;
use Events\Processings\AbstractProcessing;
use FKSDB\Logging\ILogger;
use Nette\ArrayHash;
use Nette\Forms\Form;
use ServiceSchool;
use YearCalculator;

class FlagProcessing extends AbstractProcessing {

    /**
     * @var YearCalculator
     */
    private $yearCalculator;

    /**
     * @var ServiceSchool
     */
    private $serviceSchool;

    function __construct(YearCalculator $yearCalculator, ServiceSchool $serviceSchool) {
        $this->yearCalculator = $yearCalculator;
        $this->serviceSchool = $serviceSchool;
    }

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

    private function isCzSkSchool($school_id) {
        $country = $this->serviceSchool->getTable()->select('address.region.country_iso')->where(['school_id' => $school_id])->fetch();
        if (in_array($country->country_iso, ['CZ', 'SK'])) {
            return true;
        }
        return false;
    }

    private function isStudent($study_year) {
        return ($study_year === null) ? false : true;
    }
}
