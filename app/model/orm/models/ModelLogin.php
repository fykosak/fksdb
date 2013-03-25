<?php

/**
 *
 * @author Michal Koutný <xm.koutny@gmail.com>
 */
class ModelLogin extends AbstractModelSingle {

    /**
     * @return ModelPerson
     */
    public function getPerson() {
        return ModelPerson::createFromTableRow($this->ref(DbNames::TAB_PERSON, 'person_id'));
    }

    /**
     * Sets hash of the instance with correct hashing function.
     * 
     * @note Must be called after setting person_id.
     * 
     * @param string $password password
     */
    public function setHash($password) {
        $this->hash = sha1($this->person_id . md5($password));
    }

    public function resetPassword() {
        //TODO
    }

    public function sendResetNotification() {
        //TODO
    }

    public function sendCreateNotification() {
        //TODO
    }

}

?>
