<?php

namespace FKSDB\Models\ORM\Models\Fyziklani;

/**
 * @package FKSDB\Models\ORM\Models\Fyziklani
 */
interface IFyziklaniTaskReferencedModel {

    public function getFyziklaniTask(): ?ModelFyziklaniTask;
}
