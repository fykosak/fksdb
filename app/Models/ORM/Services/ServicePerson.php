<?php

namespace FKSDB\Models\ORM\Services;

use Fykosak\NetteORM\Exceptions\ModelException;
use FKSDB\Models\ORM\IModel;
use FKSDB\Models\ORM\Models\ModelPerson;

/**
 * @author Michal Koutný <xm.koutny@gmail.com>
 * @method ModelPerson|null findByPrimary($key)
 * @method ModelPerson createNewModel(array $data)
 * @method ModelPerson store(?ModelPerson $model, array $data)
 */
class ServicePerson extends OldAbstractServiceSingle {

    public function findByEmail(?string $email): ?ModelPerson {
        if (!$email) {
            return null;
        }
        /** @var ModelPerson|false $result */
        $result = $this->getTable()->where(':person_info.email', $email)->fetch();
        return $result;
    }

    /**
     * @param IModel|ModelPerson $model
     * @return void
     * @throws ModelException
     */
    public function save(IModel &$model): void {
        if (is_null($model->gender)) {
            $model->inferGender();
        }
        parent::save($model);
    }
}
