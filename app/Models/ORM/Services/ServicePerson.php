<?php

namespace FKSDB\Models\ORM\Services;

use Fykosak\NetteORM\AbstractModel;
use FKSDB\Models\ORM\Models\ModelPerson;
use Fykosak\NetteORM\AbstractService;

/**
 * @author Michal Koutný <xm.koutny@gmail.com>
 * @method ModelPerson|null findByPrimary($key)
 * @method ModelPerson createNewModel(array $data)
 */
class ServicePerson extends AbstractService {

    public function findByEmail(?string $email): ?ModelPerson {
        if (!$email) {
            return null;
        }
        /** @var ModelPerson|null $result */
        $result = $this->getTable()->where(':person_info.email', $email)->fetch();
        return $result;
    }

    /**
     * @param ModelPerson|AbstractModel|null $model
     * @param array $data
     * @return AbstractModel
     */
    public function storeModel(array $data, ?AbstractModel $model = null): AbstractModel {
        if (is_null($model) && is_null($data['gender'])) {
            $data['gender'] = ModelPerson::inferGender($data);
        }
        return parent::storeModel($data, $model);
    }
}
