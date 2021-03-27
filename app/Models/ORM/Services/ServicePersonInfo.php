<?php

namespace FKSDB\Models\ORM\Services;

use DateTime;
use Fykosak\NetteORM\Exceptions\ModelException;
use Fykosak\NetteORM\AbstractModel;
use FKSDB\Models\ORM\Models\ModelPersonInfo;
use Fykosak\NetteORM\AbstractService;

/**
 * @author Michal Koutný <xm.koutny@gmail.com>
 * @method ModelPersonInfo refresh(AbstractModel $model)
 * @method ModelPersonInfo findByPrimary($key)
 */
class ServicePersonInfo extends AbstractService {

    public function createNewModel(array $data): ModelPersonInfo {
        if (isset($data['agreed']) && $data['agreed'] == '1') {
            $data['agreed'] = new DateTime();
        }
        return parent::createNewModel($data);
    }

    /**
     * @param AbstractModel|ModelPersonInfo $model
     * @param array $data
     * @return bool
     * @throws ModelException
     */
    public function updateModel(AbstractModel $model, array $data): bool {
        if (isset($data['agreed'])) {
            if ($data['agreed'] == '1') {
                $data['agreed'] = new DateTime();
            } elseif ($data['agreed'] == '0') {
                unset($data['agreed']);
            }
        }
        return parent::updateModel($model, $data);
    }
}
