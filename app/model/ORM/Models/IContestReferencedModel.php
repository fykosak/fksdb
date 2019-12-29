<?php

namespace FKSDB\ORM\Models;

/**
 * Interface IContestReferencedModel
 * @package FKSDB\ORM\Models
 */
interface IContestReferencedModel {
    /**
     * @return ModelContest
     */
    public function getContest(): ModelContest;
}
