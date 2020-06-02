<?php

namespace FKSDB\Events\Spec\Fol;

use FKSDB\Events\Machine\Machine;
use FKSDB\Events\Model\Holder\Holder;
use FKSDB\Events\Processings\AbstractProcessing;
use FKSDB\Logging\ILogger;
use FKSDB\ORM\Services\ServiceSchool;
use FKSDB\YearCalculator;
use Nette\Forms\Controls\BaseControl;
use Nette\Forms\Form;
use Nette\Utils\ArrayHash;

/**
 * Class FlagProcessing
 * *
 */
class FlagProcessing extends AbstractProcessing {

    private YearCalculator $yearCalculator;

    private ServiceSchool $serviceSchool;

    /**
     * FlagProcessing constructor.
     * @param YearCalculator $yearCalculator
     * @param ServiceSchool $serviceSchool
     */
    public function __construct(YearCalculator $yearCalculator, ServiceSchool $serviceSchool) {
        $this->yearCalculator = $yearCalculator;
        $this->serviceSchool = $serviceSchool;
    }

    protected function _process(array $states, ArrayHash $values, Machine $machine, Holder $holder, ILogger $logger, ?Form $form): void {
        if (!isset($values['team'])) {
            return;
        }

        $event = $holder->getPrimaryHolder()->getEvent();
        $contest = $event->getEventType()->contest;
        $year = $event->year;
        $acYear = $this->yearCalculator->getAcademicYear($contest, $year);

        foreach ($holder->getBaseHolders() as $name => $baseHolder) {
            if ($name == 'team') {
                continue;
            }
            /** @var BaseControl[] $formControls */
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
            if (!($this->serviceSchool->isCzSkSchool($participantData['school_id']) && $this->isStudent($participantData['study_year']))) {
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
     * @param mixed $studyYear
     * @return bool
     */
    private function isStudent($studyYear): bool {
        return ($studyYear === null) ? false : true;
    }
}
