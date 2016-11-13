<?php

namespace Events\Spec\Fyziklani;

use Events\Machine\BaseMachine;
use Events\Machine\Machine;
use Events\Model\Holder\Holder;
use Events\Processings\AbstractProcessing;
use Events\SubmitProcessingException;
use FKS\Logging\ILogger;
use Nette\ArrayHash;
use Nette\Diagnostics\Debugger;
use Nette\Forms\Form;
use YearCalculator;

/**
 * Na Fyziklani 2013 jsme se rozhodli pocitat tymum automaticky kategorii ve ktere soutezi podle pravidel.
 *
 * @author Aleš Podolník <ales@fykos.cz>
 * @author Michal Koutný <michal@fykos.cz> (ported to FKSDB)
 */
class CategoryProcessing extends AbstractProcessing
{

    /**
     * @var YearCalculator
     */
    private $yearCalculator;

    function __construct(YearCalculator $yearCalculator)
    {
        $this->yearCalculator = $yearCalculator;
    }

    protected function _process($states, ArrayHash $values, Machine $machine, Holder $holder, ILogger $logger, Form $form = null)
    {

        if (!isset($values['team'])) {
            return;
        }


        $event = $holder->getEvent();
        $contest = $event->getEventType()->contest;
        $year = $event->year;
        $acYear = $this->yearCalculator->getAcademicYear($contest, $year);

        $participants = array();
        foreach ($holder as $name => $baseHolder) {
            if ($name == 'team') {
                continue;
            }
            $formControl = $this->getControl("$name.person_id.person_history.study_year");
            $formControl = reset($formControl);
            $formValue = $formControl ? $formControl->getValue() : null;

            if (!$formValue) {
                if ($this->isBaseReallyEmpty($name)) {
                    continue;
                }
                $person = $baseHolder->getModel()->getMainModel()->person;
                $studyYear = $person->related('person_history')->where('ac_year', $acYear)->fetch()->study_year;
            } else {
                $studyYear = $formValue;
            }
            $participants[] = $studyYear;
        }

        Debugger::barDump($form->getValues());
        $result = $values['team']['category'] = $form->getValues()->team->force_a ? "A" : $this->getCategory($participants);
        Debugger::barDump($values);
        $original = $holder->getPrimaryHolder()->getModelState() != BaseMachine::STATE_INIT ? $holder->getPrimaryHolder()->getModel()->category : null;
        if ($original != $result) {
            $logger->log(sprintf(_('Tým zařazen do kategorie %s.'), $result), ILogger::INFO);
        }
    }

    private function getCategory($participants)
    {
        $coefficient_sum = 0;
        $count_4 = 0;
        $count_3 = 0;

        foreach ($participants as $studyYear) {
            $coefficient = ($studyYear >= 1 && $studyYear <= 4) ? $studyYear : 0;
            $coefficient_sum += $coefficient;

            if ($coefficient == 4)
                $count_4++;
            else if ($coefficient == 3)
                $count_3++;
        }


        $category_handle = $participants ? ($coefficient_sum / count($participants)) : 999;

        if ($category_handle <= 2 && $count_4 == 0 && $count_3 <= 2) {
            $result = 'C';
        } else if ($category_handle <= 3 && $count_4 <= 2) {
            $result = 'B';
        } else if ($category_handle <= 4) {
            $result = 'A';
        } else {
            throw new SubmitProcessingException(_('Nelze spočítat kategorii.'));
        }
        return $result;
    }

}
