<?php

namespace FKSDB\Models\ORM\Services;

use DateTime;
use FKSDB\Models\Exceptions\ModelException;
use FKSDB\Models\ORM\Models\AbstractModelSingle;
use FKSDB\Models\ORM\Models\ModelPerson;
use FKSDB\Models\ORM\Models\ModelPersonInfo;

/**
 * @author Michal Koutný <xm.koutny@gmail.com>
 * @method ModelPersonInfo refresh(AbstractModelSingle $model)
 * @method ModelPersonInfo findByPrimary($key)
 */
class ServicePersonInfo extends AbstractServiceSingle {

    public function createNewModel(array $data): ModelPersonInfo {
        if (isset($data['agreed']) && $data['agreed'] == '1') {
            $data['agreed'] = new DateTime();
        }
        return parent::createNewModel($data);
    }

    /**
     * @param AbstractModelSingle|ModelPersonInfo $model
     * @param array $data
     * @return bool
     * @throws ModelException
     */
    public function updateModel2(AbstractModelSingle $model, array $data): bool {
        if (isset($data['agreed'])) {
            if ($data['agreed'] == '1') {
                $data['agreed'] = new DateTime();
            } elseif ($data['agreed'] == '0') {
                unset($data['agreed']);
            }
        }
        return parent::updateModel2($model, $data);
    }

    public function store(ModelPerson $person, ?ModelPersonInfo $info, array $data): ModelPersonInfo {
        if ($info) {
            $this->updateModel2($info, $data);
            return $this->refresh($info);
        } else {
            $data['person_id'] = $person->person_id;
            return $this->createNewModel($data);
        }
    }
}
