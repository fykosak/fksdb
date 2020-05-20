<?php

namespace FKSDB\ORM\Models;
/**
 * Interface ITaskReferencedModel
 * @package FKSDB\ORM\Models
 */
interface ITaskReferencedModel {
    public function getTask(): ModelTask;
}
