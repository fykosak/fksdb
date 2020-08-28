<?php

namespace FKSDB\ORM\Models;

/**
 * Interface IContestReferencedModel
 * @author Michal Červeňák <miso@fykos.cz>
 */
interface IContestReferencedModel {
    public function getContest(): ?ModelContest;
}
