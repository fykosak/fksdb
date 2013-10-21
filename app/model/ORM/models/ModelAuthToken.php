<?php

/**
 *
 * @author Michal Koutný <xm.koutny@gmail.com>
 */
class ModelAuthToken extends AbstractModelSingle {

    const TYPE_INITIAL_LOGIN = 'initial_login'; // for login after creating login by the system
    const TYPE_SSO = 'sso';

    /**
     * @return ModelLogin
     */
    public function getLogin() {
        $data = $this->login;
        return ModelLogin::createFromTableRow($data);
    }

}
