<?php

namespace FKSDB\ORM\Services;

use FKSDB\ORM\AbstractServiceSingle;
use FKSDB\ORM\DbNames;
use FKSDB\ORM\IModel;
use FKSDB\ORM\Models\ModelPerson;

/**
 * @author Michal Koutný <xm.koutny@gmail.com>
 */
class ServicePerson extends AbstractServiceSingle {

    protected $tableName = DbNames::TAB_PERSON;
    protected $modelClassName = 'FKSDB\ORM\Models\ModelPerson';

    /**
     * Syntactic sugar.
     *
     * @param mixed $email
     * @return \FKSDB\ORM\Models\ModelPerson|null
     */
    public function findByEmail($email) {
        if (!$email) {
            return null;
        }
        $result = $this->getTable()->where('person_info:email', $email)->fetch();
        return $result ? ModelPerson::createFromTableRow($result) : null;
    }

    /**
     * @param IModel $model
     * @return mixed|void
     */
    public function save(IModel &$model) {
        if (!isset($model->gender)) {
            $model->inferGender();
        }
        return parent::save($model);
    }

}

