<?php

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
        $result = $this->getTable()->where('person_info:tex_signature', $signature)->fetch();
        return $result ? : null;
    }

}

