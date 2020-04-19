<?php

namespace FKSDB\ORM\Services;

use FKSDB\ORM\AbstractServiceSingle;
use FKSDB\ORM\DbNames;
use FKSDB\ORM\Models\ModelContest;
use FKSDB\ORM\Models\ModelQuest;

/**
 * 
 * @author Miroslav Jarý <mira.jary@gmail.com>
 *
 */
class ServiceQuest extends AbstractServiceSingle {
    /**
     * 
     * {@inheritDoc}
     * @see \FKSDB\ORM\AbstractServiceSingle::getModelClassName()
     */
    public function getModelClassName(): string {
        return ModelQuest::class;
    }
    /**
     * 
     * {@inheritDoc}
     * @see \FKSDB\ORM\AbstractServiceSingle::getTableName()
     */
    protected function getTableName(): string {
        return DbNames::TAB_QUEST;
    }

    /**
     * Syntactic sugar.
     *
     * @param \FKSDB\ORM\Models\ModelContest $contest
     * @param int $year
     * @param int $series
     * @param int $tasknr
     * @return \FKSDB\ORM\Models\ModelTask|null
     */
    public function findBySeries(ModelContest $contest, $year, $series, $tasknr, $questnr) {
        $result = $this->getTable()->where([
            'contest_id' => $contest->contest_id,
            'year' => $year,
            'series' => $series,
            'tasknr' => $tasknr,
            'questnr' => $questnr,
        ])->fetch();

        if ($result !== false) {
            return ModelQuest::createFromActiveRow($result);
        } else {
            return null;
        }
    }


}
