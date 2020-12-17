<?php

namespace FKSDB\Model\Events\Spec\Fyziklani;

use FKSDB\Model\Events\Machine\Machine;
use FKSDB\Model\Events\Model\Holder\Holder;
use FKSDB\Model\Events\Spec\AbstractCategoryProcessing;
use FKSDB\Model\Events\Exceptions\SubmitProcessingException;
use FKSDB\Model\Logging\ILogger;
use FKSDB\Model\Messages\Message;
use FKSDB\Model\ORM\Models\Fyziklani\ModelFyziklaniTeam;
use Nette\Forms\Form;
use Nette\Utils\ArrayHash;

/**
 * Na Fyziklani 2013 jsme se rozhodli pocitat tymum automaticky kategorii ve ktere soutezi podle pravidel.
 *
 * @author Aleš Podolník <ales@fykos.cz>
 * @author Michal Koutný <michal@fykos.cz> (ported to FKSDB)
 */
class CategoryProcessing extends AbstractCategoryProcessing {

    protected function innerProcess(array $states, ArrayHash $values, Machine $machine, Holder $holder, ILogger $logger, ?Form $form): void {
        if (!isset($values['team'])) {
            return;
        }
        if ($values['team']['force_a']) {
            $values['team']['category'] = ModelFyziklaniTeam::CATEGORY_HIGH_SCHOOL_A;
        } else {
            $participants = $this->extractValues($holder);
            $values['team']['category'] = $this->getCategory($participants);
        }

        $values['team']['category'] = $values['team']['force_a'] ? "A" : $this->getCategory($participants);
        $original = $holder->getPrimaryHolder()->getModelState() != \FKSDB\Model\Transitions\Machine\Machine::STATE_INIT ? $holder->getPrimaryHolder()->getModel()->category : null;

        if ($original != $values['team']['category']) {
            $logger->log(new Message(sprintf(_('Team inserted to category %s.'), ModelFyziklaniTeam::mapCategoryToName($values['team']['category'])), ILogger::INFO));
        }
    }

    protected function getCategory(array $participants): string {
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
            throw new SubmitProcessingException(_('Cannot determine category.'));
        }
        return $result;
    }
}