<?php

namespace FKSDB\Models\ORM\Services;

use FKSDB\Models\ORM\Models\AbstractModelSingle;
use FKSDB\Models\ORM\Models\ModelPerson;
use FKSDB\Models\ORM\Models\ModelPersonHistory;

/**
 * @author Michal Koutný <xm.koutny@gmail.com>
 * @method ModelPersonHistory createNewModel(array $data)
 * @method ModelPersonHistory refresh(AbstractModelSingle $model)
 */
class ServicePersonHistory extends AbstractServiceSingle {

    public function store(ModelPerson $person, ?ModelPersonHistory $history, array $data, int $acYear): ModelPersonHistory {
        if ($history) {
            $this->updateModel2($history, $data);
            return $this->refresh($history);
        } else {
            return $this->createNewModel(array_merge($data, [
                'ac_year' => $acYear,
                'person_id' => $person->person_id,
            ]));
        }
    }
}
