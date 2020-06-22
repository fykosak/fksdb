<?php

namespace FKSDB\Events\Spec\Fyziklani;

use FKSDB\Events\Machine\BaseMachine;
use FKSDB\Events\Machine\Machine;
use FKSDB\Events\Model\Holder\Holder;
use FKSDB\Events\Spec\AbstractCategoryProcessing;
use FKSDB\Events\SubmitProcessingException;
use FKSDB\Logging\ILogger;
use FKSDB\Messages\Message;
use FKSDB\ORM\Models\Fyziklani\ModelFyziklaniTeam;
use Nette\Forms\Form;
use Nette\Utils\ArrayHash;


/**
 * Na Fyziklani 2013 jsme se rozhodli pocitat tymum automaticky kategorii ve ktere soutezi podle pravidel.
 *
 * @author Aleš Podolník <ales@fykos.cz>
 * @author Michal Koutný <michal@fykos.cz> (ported to FKSDB)
 */
class CategoryProcessing extends AbstractCategoryProcessing {

    /**
     * @param $states
     * @param ArrayHash $values
     * @param Machine $machine
     * @param Holder $holder
     * @param ILogger $logger
     * @param Form|null $form
     * @return void
     */
    protected function _process($states, ArrayHash $values, Machine $machine, Holder $holder, ILogger $logger, Form $form = null) {

        if (!isset($values['team'])) {
            return;
        }
        if ($values['team']['force_a']) {
            $values['team']['category'] = 'A';
        } else {
            $participants = $this->extractValues($holder);
            $values['team']['category'] = $this->getCategory($participants);
        }

        $original = $holder->getPrimaryHolder()->getModelState() != BaseMachine::STATE_INIT ? $holder->getPrimaryHolder()->getModel()->category : null;

        if ($original != $values['team']['category']) {
            $logger->log(new Message(sprintf(_('Tým zařazen do kategorie %s.'), ModelFyziklaniTeam::mapCategoryToName($values['team']['category'])), ILogger::INFO));
        }
    }

    private function getCategory(array $participants): string {
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
            $result = ModelFyziklaniTeam::CATEGORY_HIGH_SCHOOL_C;
        } elseif ($categoryHandle <= 3 && $count4 <= 2) {
            $result = ModelFyziklaniTeam::CATEGORY_HIGH_SCHOOL_B;
        } elseif ($categoryHandle <= 4) {
            $result = ModelFyziklaniTeam::CATEGORY_HIGH_SCHOOL_A;
        } else {
            throw new SubmitProcessingException(_('Nelze spočítat kategorii.'));
        }
        return $result;
    }
}
