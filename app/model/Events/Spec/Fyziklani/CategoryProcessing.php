<?php

namespace Events\Spec\Fyziklani;

use Events\Machine\BaseMachine;
use Events\Machine\Machine;
use Events\Model\Holder\Holder;
use Events\Processings\AbstractProcessing;
use Events\SubmitProcessingException;
use FKSDB\Logging\ILogger;
use FKSDB\ORM\Services\ServiceSchool;
use FKSDB\YearCalculator;
use Nette\Forms\Form;
use Nette\Utils\ArrayHash;


/**
 * Na Fyziklani 2013 jsme se rozhodli pocitat tymum automaticky kategorii ve ktere soutezi podle pravidel.
 *
 * @author Aleš Podolník <ales@fykos.cz>
 * @author Michal Koutný <michal@fykos.cz> (ported to FKSDB)
 */
class CategoryProcessing extends AbstractProcessing {

    /**
     * @var \FKSDB\YearCalculator
     */
    private $yearCalculator;

    /**
     * @var ServiceSchool
     */
    private $serviceSchool;

    /**
     * CategoryProcessing constructor.
     * @param \FKSDB\YearCalculator $yearCalculator
     * @param ServiceSchool $serviceSchool
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

        $participants = [];
        foreach ($holder as $name => $baseHolder) {
            if ($name == 'team') {
                continue;
            }
            $studyYearControl = $this->getControl("$name.person_id.person_history.study_year");
            $schoolControl = $this->getControl("$name.person_id.person_history.school_id");

            $studyYearControl = reset($studyYearControl);
            $schoolControl = reset($schoolControl);

            $schoolValue = $schoolControl ? $schoolControl->getValue() : null;
            $studyYearValue = $studyYearControl ? $studyYearControl->getValue() : null;

            if (!$studyYearValue) {
                if ($this->isBaseReallyEmpty($name)) {
                    continue;
                }
                /**
                 * @var \FKSDB\ORM\Models\ModelPerson $person
                 */
                $person = $baseHolder->getModel()->getMainModel()->person;
                $history = $person->related('person_history')->where('ac_year', $acYear)->fetch();
                $participantData = [
                    'school_id' => $history->school_id,
                    'study_year' => $history->study_year,
                ];

            } else {
                $participantData = [
                    'school_id' => $schoolValue,
                    'study_year' => $studyYearValue,
                ];
            }
            $participants[] = $participantData;
        }

        $values['team']['category'] = $values['team']['force_a'] ? "A" : $this->getCategory($participants);
        $original = $holder->getPrimaryHolder()->getModelState() != BaseMachine::STATE_INIT ? $holder->getPrimaryHolder()->getModel()->category : null;

        if ($original != $values['team']['category']) {
            $logger->log(sprintf(_('Tým zařazen do kategorie %s.'), $values['team']['category']), ILogger::INFO);
        }
    }

    /**
     * @param $participants
     * @return string
     */
    private function getCategory($participants) {
        $coefficientSum = 0;
        $count4 = 0;
        $count3 = 0;

        foreach ($participants as $participant) {
            $studyYear = $participant['study_year'];
            $coefficient = ($studyYear >= 1 && $studyYear <= 4) ? $studyYear : 0;
            $coefficientSum += $coefficient;

            if ($coefficient == 4) {
                $count4++;
            } elseif ($coefficient == 3) {
                $count3++;
            }
        }

        $categoryHandle = $participants ? ($coefficientSum / count($participants)) : 999;

        // if ($abroad > 0) {
        //     $result = 'F';
        // } else
        if ($categoryHandle <= 2 && $count4 == 0 && $count3 <= 2) {
            $result = 'C';
        } elseif ($categoryHandle <= 3 && $count4 <= 2) {
            $result = 'B';
        } elseif ($categoryHandle <= 4) {
            $result = 'A';
        } else {
            throw new SubmitProcessingException(_('Nelze spočítat kategorii.'));
        }
        return $result;
    }

}
