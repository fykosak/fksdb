<?php

namespace FKSDB\Models\ORM\Services;

use FKSDB\Models\ORM\Models\ModelTeacher;

/**
 * @author Michal Červeňák <miso@fykos.cz>
 */
class ServiceTeacher extends AbstractServiceSingle {

    public function store(?ModelTeacher $model, array $data): ModelTeacher {
        if (is_null($model)) {
            return $this->createNewModel($data);
        } else {
            $this->updateModel2($model, $data);
            return $this->refresh($model);
        }
    }
}
