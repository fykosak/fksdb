<?php

declare(strict_types=1);

namespace FKSDB\Models\Events\Spec\Fyziklani;

use FKSDB\Models\Events\Exceptions\SubmitProcessingException;
use FKSDB\Models\Events\Model\Holder\Holder;
use FKSDB\Models\Events\Spec\AbstractCategoryProcessing;
use Fykosak\Utils\Logging\Logger;
use Fykosak\Utils\Logging\Message;
use FKSDB\Models\ORM\Models\Fyziklani\TeamModel;
use Nette\Forms\Form;
use Nette\Utils\ArrayHash;

/**
 * Na Fyziklani 2013 jsme se rozhodli pocitat tymum automaticky kategorii ve ktere soutezi podle pravidel.
 */
class CategoryProcessing extends AbstractCategoryProcessing
{

    protected function innerProcess(array $states, ArrayHash $values, Holder $holder, Logger $logger, ?Form $form): void
    {
        if (!isset($values['team'])) {
            return;
        }
        if ($values['team']['force_a']) {
            $values['team']['category'] = TeamModel::CATEGORY_HIGH_SCHOOL_A;
        } else {
            $participants = $this->extractValues($holder);
            $values['team']['category'] = $this->getCategory($participants);
        }
        // TODO hack if all study year fields are disabled
        $model = $holder->primaryHolder->getModel2();
        /** @var TeamModel $model */
        $original = $model ? $model->category : null;

        if ($original != $values['team']['category']) {
            $logger->log(
                new Message(
                    sprintf(
                        _('Team inserted to category %s.'),
                        TeamModel::mapCategoryToName($values['team']['category'])
                    ),
                    Message::LVL_INFO
                )
            );
        }
    }

    protected function getCategory(array $participants): string
    {
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
            $result = TeamModel::CATEGORY_HIGH_SCHOOL_C;
        } elseif ($categoryHandle <= 3 && $count4 <= 2) {
            $result = TeamModel::CATEGORY_HIGH_SCHOOL_B;
        } elseif ($categoryHandle <= 4) {
            $result = TeamModel::CATEGORY_HIGH_SCHOOL_A;
        } else {
            throw new SubmitProcessingException(_('Cannot determine category.'));
            //$result = ModelFyziklaniTeam::CATEGORY_HIGH_SCHOOL_A; // TODO hack if all study year fields are disabled
        }
        return $result;
    }
}
