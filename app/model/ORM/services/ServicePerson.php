<?php

use FKSDB\ORM\ModelPerson;
use ORM\IModel;

/**
 * @author Michal Koutný <xm.koutny@gmail.com>
 */
class ServicePerson extends AbstractServiceSingle {

    protected $tableName = DbNames::TAB_PERSON;
    protected $modelClassName = 'FKSDB\ORM\ModelPerson';

    /**
     * Syntactic sugar.
     *
     * @param mixed $email
     * @return ModelPerson|null
     */
    public function findByEmail($email) {
        if (!$email) {
            return null;
        }
        $result = $this->getTable()->where('person_info:email', $email)->fetch();
        return $result ? ModelPerson::createFromTableRow($result) : null;
    }

    public function save(IModel &$model) {
        if (!isset($model->gender)) {
            $model->inferGender();
        }
        return parent::save($model);
    }

}

