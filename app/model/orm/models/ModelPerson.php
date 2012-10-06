<?php

/**
 *
 * @author Michal Koutný <xm.koutny@gmail.com>
 */
class ModelPerson extends AbstractModelSingle {

    /**
     * @return AbstractModelSingle|null
     */
    public function getLogin() {
        return $this->ref(DbNames::TAB_LOGIN, 'person_id');
    }

    /**
     * @return AbstractModelSingle|null
     */
    public function getInfo() {
        return $this->ref(DbNames::TAB_PERSON_INFO, 'person_id');
    }

}

?>
