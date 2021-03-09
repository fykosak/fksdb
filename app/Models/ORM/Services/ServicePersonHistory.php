<?php

namespace FKSDB\Models\ORM\Services;

use Fykosak\NetteORM\AbstractModel;
use FKSDB\Models\ORM\Models\ModelPerson;
use FKSDB\Models\ORM\Models\ModelPersonHistory;
use Fykosak\NetteORM\AbstractService;

/**
 * @author Michal Koutný <xm.koutny@gmail.com>
 * @method ModelPersonHistory createNewModel(array $data)
 * @method ModelPersonHistory refresh(AbstractModel $model)
 */
class ServicePersonHistory extends AbstractService {

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
