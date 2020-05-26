<?php

namespace FKSDB\ORM\Models;

use FKSDB\ORM\Models\Fyziklani\ModelFyziklaniTask;

/**
 * Interface IFyziklaniTaskReferencedModel
 * *
 */
interface IFyziklaniTaskReferencedModel {
    public function getFyziklaniTask(): ModelFyziklaniTask;
}
