<?php

namespace FKSDB\Models\ORM\Models\Fyziklani;
use Fykosak\NetteORM\AbstractModel;

/**
 * @property-read string name
 * @property-read string label
 * @property-read int fyziklani_task_id
 */
class ModelFyziklaniTask extends AbstractModel {

    public function __toArray(bool $hideName = false): array {
        return [
            'label' => $this->label,
            'taskId' => $this->fyziklani_task_id,
            'name' => $hideName ? null : $this->name,
        ];
    }
}
