<?php

namespace FKSDB\ORM\Services;

use FKSDB\ORM\AbstractModelSingle;
use FKSDB\ORM\AbstractServiceSingle;
use FKSDB\ORM\DbNames;
use FKSDB\ORM\Models\ModelPerson;
use FKSDB\ORM\Models\ModelPersonHistory;

/**
 * @author Michal Koutný <xm.koutny@gmail.com>
 * @method ModelPersonHistory createNewModel(array $data)
 * @method ModelPersonHistory refresh(AbstractModelSingle $model)
 */
class ServicePersonHistory extends AbstractServiceSingle {

    public function getModelClassName(): string {
        return ModelPersonHistory::class;
    }

    protected function getTableName(): string {
        return DbNames::TAB_PERSON_HISTORY;
    }

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
