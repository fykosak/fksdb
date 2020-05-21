<?php

namespace FKSDB\ORM\Models;

use FKSDB\ORM\Models\Fyziklani\ModelFyziklaniTask;

/**
 * Interface IFyziklaniTaskReferencedModel
 * @package FKSDB\ORM\Models
 */
interface IFyziklaniTaskReferencedModel {
    public function getFyziklaniTask(): ModelFyziklaniTask;
}
