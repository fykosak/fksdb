<?php

namespace FKSDB\ORM\Models;
/**
 * Interface ITaskReferencedModel
 * *
 */
interface ITaskReferencedModel {
    public function getTask(): ModelTask;
}
