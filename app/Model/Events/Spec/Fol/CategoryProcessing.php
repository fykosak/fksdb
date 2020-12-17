<?php

namespace FKSDB\Model\Events\Spec\Fol;

use FKSDB\Model\Events\Machine\Machine;
use FKSDB\Model\Events\Model\Holder\Holder;
use FKSDB\Model\Events\Spec\AbstractCategoryProcessing;
use FKSDB\Model\Logging\ILogger;
use FKSDB\Model\Messages\Message;
use FKSDB\Model\ORM\Models\Fyziklani\ModelFyziklaniTeam;
use FKSDB\Model\ORM\Models\ModelRegion;
use FKSDB\Model\ORM\Services\ServiceSchool;
use FKSDB\Model\YearCalculator;
use Nette\Forms\Form;
use Nette\InvalidArgumentException;
use Nette\Utils\ArrayHash;

/**
 * Class CategoryProcessing
 * *
 */
class CategoryProcessing extends AbstractCategoryProcessing {

    private int $rulesVersion;

    public function __construct(int $rulesVersion, YearCalculator $yearCalculator, ServiceSchool $serviceSchool) {
        parent::__construct($yearCalculator, $serviceSchool);

        if (!in_array($rulesVersion, [1, 2])) {
            throw new InvalidArgumentException(_('Not valid $rulesVersion.'));
        }
        $this->rulesVersion = $rulesVersion;
    }

    protected function innerProcess(array $states, ArrayHash $values, Machine $machine, Holder $holder, ILogger $logger, ?Form $form): void {
        if (!isset($values['team'])) {
            return;
        }
        $participants = $this->extractValues($holder);

        $result = $values['team']['category'] = $this->getCategory($participants);

        $original = $holder->getPrimaryHolder()->getModelState() != \FKSDB\Model\Transitions\Machine\Machine::STATE_INIT ? $holder->getPrimaryHolder()->getModel()->category : null;
        if ($original != $result) {
            $logger->log(new Message(sprintf(_('Team inserted to category %s.'), ModelFyziklaniTeam::mapCategoryToName($result)), ILogger::INFO));
        }
    }

    /*
     *   Open (staří odkudkoliv - pokazí to i jeden člen týmu)
     *   Zahraniční
     *   ČR - A - (3,4]
     *   ČR - B - (2,3] - max. 2 ze 4. ročníku
     *   ČR - C - [0,2] - nikdo ze 4. ročníku, max. 2 z 3 ročníku
     */
    protected function getCategory(array $competitors): string {
        // init stats
        $olds = 0;
        $year = [0, 0, 0, 0, 0]; //0 - ZŠ, 1..4 - SŠ
        $abroad = 0;
        // calculate stats
        foreach ($competitors as $competitor) {
            if (!$competitor['school_id']) { // for future
                $olds += 1;
            } else {
                /** @var ModelRegion|false $country */
                $country = $this->serviceSchool->getTable()->select('address.region.country_iso')->where(['school_id' => $competitor['school_id']])->fetch();
                if (!in_array($country->country_iso, ['CZ', 'SK'])) {
                    $abroad += 1;
                }
            }

            if ($competitor['study_year'] === null) {
                $olds += 1;
            } elseif ($competitor['study_year'] >= 1 && $competitor['study_year'] <= 4) {
                $year[(int)$competitor['study_year']] += 1;
            } else {
                $year[0] += 1; // ZŠ
            }
        }
        // evaluate stats
        if ($olds > 0) {
            return ModelFyziklaniTeam::CATEGORY_OPEN;
        } elseif ($this->rulesVersion == 1 && $abroad > 0) {
            return ModelFyziklaniTeam::CATEGORY_ABROAD;
        } else { //Czech/Slovak highschoolers (or lower)
            $sum = 0;
            $cnt = 0;
            for ($y = 0; $y <= 4; ++$y) {
                $sum += $year[$y] * $y;
                $cnt += $year[$y];
            }
            $avg = $sum / $cnt;
            if ($avg <= 2 && $year[4] == 0 && $year[3] <= 2) {
                return ModelFyziklaniTeam::CATEGORY_HIGH_SCHOOL_C;
            } elseif ($avg <= 3 && $year[4] <= 2) {
                return ModelFyziklaniTeam::CATEGORY_HIGH_SCHOOL_B;
            } else {
                return ModelFyziklaniTeam::CATEGORY_HIGH_SCHOOL_A;
            }
        }
    }
}