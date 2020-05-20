<?php

namespace FKSDB\ORM\Models;

/**
 * Interface IContestReferencedModel
 * @package FKSDB\ORM\Models
 */
interface IContestReferencedModel {
    public function getContest(): ModelContest;
}
