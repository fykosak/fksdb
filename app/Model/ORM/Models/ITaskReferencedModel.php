<?php

namespace FKSDB\Model\ORM\Models;
/**
 * Interface ITaskReferencedModel
 * @author Michal Červeňák <miso@fykos.cz>
 */
interface ITaskReferencedModel {
    public function getTask(): ?ModelTask;
}
