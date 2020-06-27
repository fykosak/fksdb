<?php

namespace FKSDB\ORM\Services;

use FKSDB\ORM\AbstractServiceSingle;
use FKSDB\ORM\DbNames;
use FKSDB\ORM\IModel;
use FKSDB\ORM\Models\ModelPerson;

/**
 * @author Michal Koutný <xm.koutny@gmail.com>
 * @method ModelPerson|null findByPrimary($key)
 * @method ModelPerson createNewModel(array $data)
 */
class ServicePerson extends AbstractServiceSingle {

    public function getModelClassName(): string {
        return ModelPerson::class;
    }

    protected function getTableName(): string {
        return DbNames::TAB_PERSON;
    }

    /**
     * Syntactic sugar.
     *
     * @param string $email
     * @return ModelPerson|null
     */
    public function findByEmail($email) {
        if (!$email) {
            return null;
        }
        /** @var ModelPerson|false $result */
        $result = $this->getTable()->where(':person_info.email', $email)->fetch();
        return $result ?: null;
    }

    /**
     * @param IModel|ModelPerson $model
     * @return void
     */
    public function save(IModel &$model) {
        if (is_null($model->gender)) {
            $model->inferGender();
        }
        parent::save($model);
    }

}
