<?php

namespace FKSDB\ORM\Models\Fyziklani;

/**
 * Interface IFyziklaniTaskReferencedModel
 * *
 */
interface IFyziklaniTaskReferencedModel {
    public function getFyziklaniTask(): ?ModelFyziklaniTask;
}
