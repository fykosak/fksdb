<?php

namespace Events\Spec\Fyziklani;

use Events\Machine\Machine;
use Events\Model\Holder\Holder;
use Events\Processings\AbstractProcessing;
use Nette\ArrayHash;
use Submits\ProcessingException;
use YearCalculator;

/**
 * Na Fyziklani 2013 jsme se rozhodli pocitat tymum automaticky kategorii ve ktere soutezi podle pravidel.
 * 
 * @author Aleš Podolník <ales@fykos.cz>
 * @author Michal Koutný <michal@fykos.cz> (only ported to FKSDB)
 */
class CategoryProcessing extends AbstractProcessing {

    /**
     * @var YearCalculator
     */
    private $yearCalculator;

    function __construct(YearCalculator $yearCalculator) {
        $this->yearCalculator = $yearCalculator;
    }

    protected function _process(ArrayHash $values, Machine $machine, Holder $holder) {
        $event = $holder->getEvent();
        $contest = $event->getEventType()->contest;
        $year = $event->year;
        $acYear = $this->yearCalculator->getAcademicYear($contest, $year);

// Aleš input:
        $maxYear = $acYear - 1;
        $participants = array();
        foreach ($this->getValue('p*.person_history.study_year') as $studyYear) {
            $participants[] = $this->yearCalculator->getGraduationYear($studyYear, $acYear);
        };

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
            $values['team']['category'] = 'C';
        } else if ($category_handle <= 3 && $count_4 <= 2) {
            $values['team']['category'] = 'B';
        } else if ($category_handle <= 4) {
            $values['team']['category'] = 'A';
        } else {
            throw new ProcessingException(_('Nelze spočítat kategorii.'));
        }


        // konec kodu pocitajiciho kategorii
    }

}
