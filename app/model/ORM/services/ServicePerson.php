<?php

use ORM\IModel;

/**
 * @author Michal Koutný <xm.koutny@gmail.com>
 */
class ServicePerson extends AbstractServiceSingle {

    protected $tableName = DbNames::TAB_PERSON;
    protected $modelClassName = 'ModelPerson';

    /**
     * Syntactic sugar.
     * 
     * @param type $signature
     * @return ModelPerson|null
     */
    public function findByTeXSignature($signature) {
        if (!$signature) {
            return null;
        }
        $result = $this->getTable()->where('person_info:tex_signature', $signature)->fetch();
        return $result ? : null;
    }

    /**
     * Syntactic sugar.
     * 
     * @param type $email
     * @return ModelPerson|null
     */
    public function findByEmail($email) {
        if (!$email) {
            return null;
        }
        $result = $this->getTable()->where('person_info:email', $email)->fetch();
        return $result ? : null;
    }

    public function save(IModel &$model) {
        if (!isset($model->gender)) {
            $model->inferGender();
        }
        return parent::save($model);
    }

}

