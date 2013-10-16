<?php

/**
 *
 * @author Michal Koutný <xm.koutny@gmail.com>
 */
class ModelOrg extends AbstractModelSingle {

    /**
     * @return ModelContest
     */
    public function getContest() {
        $data = $this->contest;
        return ModelContest::createFromTableRow($data);
    }
    
    /**
     * @return ModelPerson
     */
    public function getPerson() {
        $data = $this->person;
        return ModelPerson::createFromTableRow($data);
    }

}

?>
