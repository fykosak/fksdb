<?php

namespace FKSDB\Models\Events\Spec\Fol;

use FKSDB\Models\Events\Model\Holder\Holder;
use FKSDB\Models\Events\Spec\AbstractCategoryProcessing;
use Fykosak\Utils\Logging\Logger;
use Fykosak\Utils\Logging\Message;
use FKSDB\Models\ORM\Models\Fyziklani\ModelFyziklaniTeam;
use Nette\Forms\Form;
use Nette\Utils\ArrayHash;

class CategoryProcessing2 extends AbstractCategoryProcessing
{

    protected function innerProcess(array $states, ArrayHash $values, Holder $holder, Logger $logger, ?Form $form = null): void
    {
        if (!isset($values['team'])) {
            return;
        }

        $participants = $this->extractValues($holder);

        $result = $values['team']['category'] = $this->getCategory($participants);
        $model = $holder->getPrimaryHolder()->getModel2();
        $original = $model ? $model->category : null;
        if ($original != $result) {
            $logger->log(new Message(sprintf(_('Team registered for the category %s.'), ModelFyziklaniTeam::mapCategoryToName($result)), Message::LVL_INFO));
        }
    }

    /**
     *   Open (staří odkudkoliv - pokazí to i jeden člen týmu)
     *   Zahraniční
     *   ČR - A - (3,4]
     *   ČR - B - (2,3] - max. 2 ze 4. ročníku
     *   ČR - C - [0,2] - nikdo ze 4. ročníku, max. 2 z 3 ročníku
     */
    protected function getCategory(array $participants): string
    {
        // init stats
        $olds = 0;
        $years = [0, 0, 0, 0, 0]; //0 - ZŠ, 1..4 - SŠ
        // calculate stats
        foreach ($participants as $competitor) {
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
