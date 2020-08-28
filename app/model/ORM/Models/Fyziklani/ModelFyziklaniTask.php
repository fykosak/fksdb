<?php

namespace FKSDB\ORM\Models\Fyziklani;
use FKSDB\ORM\AbstractModelSingle;

/**
 *
 * @author Lukáš Timko <lukast@fykos.cz>
 * @property-read string name
 * @property-read string label
 * @property-read int fyziklani_task_id
 */
class ModelFyziklaniTask extends AbstractModelSingle {

    public function __toArray(bool $hideName = false): array {
        return [
            'label' => $this->label,
            'taskId' => $this->fyziklani_task_id,
            'name' => $hideName ? null : $this->name,
        ];
    }
}
