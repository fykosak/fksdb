<?php

namespace FKSDB\Models\ORM\Services;

use Fykosak\NetteORM\AbstractModel;
use FKSDB\Models\ORM\Models\ModelOrg;
use Fykosak\NetteORM\AbstractService;

/**
 * @author Michal Koutný <xm.koutny@gmail.com>
 * @method ModelOrg createNewModel(array $data)
 * @method ModelOrg refresh(AbstractModel $model)
 */
class ServiceOrg extends AbstractService {

    public function store(?ModelOrg $model, array $data): ModelOrg {
        if (is_null($model)) {
            return $this->createNewModel($data);
        } else {
            $this->updateModel2($model, $data);
            return $this->refresh($model);
        }
    }
}
