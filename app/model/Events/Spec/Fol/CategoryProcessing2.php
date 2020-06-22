<?php

namespace FKSDB\Events\Spec\Fol;

use FKSDB\Events\Machine\BaseMachine;
use FKSDB\Events\Machine\Machine;
use FKSDB\Events\Model\Holder\Holder;
use FKSDB\Events\Spec\AbstractCategoryProcessing;
use FKSDB\Logging\ILogger;
use FKSDB\Messages\Message;
use FKSDB\ORM\Models\Fyziklani\ModelFyziklaniTeam;
use Nette\Forms\Form;
use Nette\Utils\ArrayHash;

/**
 * Class CategoryProcessing2
 * @author Michal Červeňák <miso@fykos.cz>
 */
class CategoryProcessing2 extends AbstractCategoryProcessing {

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

        $participants = $this->extractValues($holder);

        $result = $values['team']['category'] = $this->getCategory($participants);

        $original = $holder->getPrimaryHolder()->getModelState() != BaseMachine::STATE_INIT ? $holder->getPrimaryHolder()->getModel()->category : null;
        if ($original != $result) {
            $logger->log(new Message(sprintf(_('Tým zařazen do kategorie %s.'), ModelFyziklaniTeam::mapCategoryToName($result)), ILogger::INFO));
        }
    }

    /**
     *   Open (staří odkudkoliv - pokazí to i jeden člen týmu)
     *   Zahraniční
     *   ČR - A - (3,4]
     *   ČR - B - (2,3] - max. 2 ze 4. ročníku
     *   ČR - C - [0,2] - nikdo ze 4. ročníku, max. 2 z 3 ročníku
     * @param array $competitors
     * @return string
     */
    private function getCategory(array $competitors): string {
        // init stats
        $olds = 0;
        $years = [0, 0, 0, 0, 0]; //0 - ZŠ, 1..4 - SŠ
        // calculate stats
        foreach ($competitors as $competitor) {
            if (!$competitor['school_id']) { // for future
                $olds += 1;
            }

            if ($competitor['study_year'] === null) {
                $olds += 1;
            } elseif ($competitor['study_year'] >= 1 && $competitor['study_year'] <= 4) {
                $years[(int)$competitor['study_year']] += 1;
            } else {
                $years[0] += 1; // ZŠ
            }
        }
        // evaluate stats
        if ($olds > 0) {
            return ModelFyziklaniTeam::CATEGORY_OPEN;
        } else {
            $sum = 0;
            $cnt = 0;
            foreach ($years as $studyYear => $count) {
                $sum += $count * $studyYear;
                $cnt += $count;
            }
            $avg = $sum / $cnt;
            if ($avg <= 2 && $years[4] == 0 && $years[3] <= 2) {
                return ModelFyziklaniTeam::CATEGORY_HIGH_SCHOOL_C;
            } elseif ($avg <= 3 && $years[4] <= 2) {
                return ModelFyziklaniTeam::CATEGORY_HIGH_SCHOOL_B;
            } else {
                return ModelFyziklaniTeam::CATEGORY_HIGH_SCHOOL_A;
            }
        }
    }
}
