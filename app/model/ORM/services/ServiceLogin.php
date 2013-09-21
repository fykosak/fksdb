<?php

/**
 * @author Michal Koutný <xm.koutny@gmail.com>
 */
class ServiceLogin extends AbstractServiceSingle {

    protected $tableName = DbNames::TAB_LOGIN;
    protected $modelClassName = 'ModelLogin';
    
    /**
     * Creates login and invites user to set up the account.
     * 
     * @param string $email
     */
    public function createLoginWithInvitation($email) {
        throw new NotImplementedException();
    }

}

