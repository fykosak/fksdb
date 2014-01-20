<?php

namespace Events\Spec\Fyziklani;

use Events\Machine\BaseMachine;
use Events\Machine\Machine;
use Events\Model\Holder\Holder;
use Events\Processings\AbstractProcessing;
use Events\SubmitProcessingException;
use Nette\ArrayHash;
use YearCalculator;

/**
 * Na Fyziklani 2013 jsme se rozhodli pocitat tymum automaticky kategorii ve ktere soutezi podle pravidel.
 * 
 * @author Aleš Podolník <ales@fykos.cz>
 * @author Michal Koutný <michal@fykos.cz> (ported to FKSDB)
 */
class CategoryProcessing extends AbstractProcessing {

    /**
     * @var YearCalculator
     */
    private $yearCalculator;

    function __construct(YearCalculator $yearCalculator) {
        $this->yearCalculator = $yearCalculator;
    }

    protected function _process(\Nette\Application\UI\Control $control, ArrayHash $values, Machine $machine, Holder $holder) {
        if (!isset($values['team'])) {
            return;
        }
        //TODO would need also sanitatio of behavior when not all forms are enabled

        $event = $holder->getEvent();
        $contest = $event->getEventType()->contest;
        $year = $event->year;
        $acYear = $this->yearCalculator->getAcademicYear($contest, $year);

// prepare input for Aleš:
        $maxYear = $acYear - 1;
        $participants = array();
        foreach ($holder as $name => $baseHolder) {
            if ($name == 'team') {
                continue;
            }
            $formValue = $this->getValue("$name.person_history.study_year");
            if (!$formValue) {
                if ($baseHolder->getModelState() == BaseMachine::STATE_INIT) {
                    continue;
                }
                $person = $baseHolder->getModel()->getMainModel()->person;
                $studyYear = $person->related('person_history')->where('ac_year', $acYear)->fetch()->study_year;
            } else {
                $studyYear = $formValue;
            }
            $participants[] = $this->yearCalculator->getGraduationYear($studyYear, $acYear);
        }

// Aleš begin

        $coefficients = array($maxYear + 4 => 0, $maxYear + 3 => 1, $maxYear + 2 => 2, $maxYear + 1 => 3, $maxYear => 4);
        $possible_years = array($maxYear + 4, $maxYear + 3, $maxYear + 2, $maxYear + 1, $maxYear);


        $coefficient_sum = 0;
        $year_error = false;
        $count_4 = 0;
        $count_3 = 0;

        foreach ($participants as $graduationYear) {
//$coefficient_sum .= $graduationYear."-".$coefficients[$graduationYear].";";

            if (in_array($graduationYear, $possible_years)) {

                $coefficient = $coefficients[$graduationYear];
                $coefficient_sum += $coefficient;

                if ($coefficient == 4)
                    $count_4++;
                else if ($coefficient == 3)
                    $count_3++;
            } else {
                $year_error = true;
            }
        }


        $category_handle = 100;

        if (!$year_error) {
            if (count($participants) == 0) {
                $category_handle = 'error';
            } else {
                $category_handle = $coefficient_sum / count($participants);
            }
        }

        if ($category_handle <= 2 && $count_4 == 0 && $count_3 <= 2) {
            $result = 'C';
        } else if ($category_handle <= 3 && $count_4 <= 2) {
            $result = 'B';
        } else if ($category_handle <= 4) {
            $result = 'A';
        } else {
            throw new SubmitProcessingException(_('Nelze spočítat kategorii.'));
        }
        $values['team']['category'] = $result;

        $original = $holder->getPrimaryHolder()->getModelState() != BaseMachine::STATE_INIT ? $holder->getPrimaryHolder()->getModel()->category : null;
        if ($original != $result) {
            $control->flashMessage(sprintf(_('Týmu zařazen do kategorie %s.'), $result));
        }


        // konec kodu pocitajiciho kategorii
    }

}
