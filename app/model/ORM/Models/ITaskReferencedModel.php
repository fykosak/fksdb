<?php

namespace FKSDB\ORM\Models;
/**
 * Interface ITaskReferencedModel
 * @package FKSDB\ORM\Models
 */
interface ITaskReferencedModel {
    /**
     * @return ModelTask
     */
    public function getTask();
}
