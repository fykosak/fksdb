<?php

namespace FKSDB\ORM\Models;

use FKSDB\ORM\Models\Fyziklani\ModelFyziklaniTask;

/**
 * Interface IFyziklaniTaskReferencedModel
 * @package FKSDB\ORM\Models
 */
interface IFyziklaniTaskReferencedModel {
    /**
     * @return ModelFyziklaniTask
     */
    public function getFyziklaniTask();
}
