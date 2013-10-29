<?php

/**
 *
 * @author Michal Koutný <xm.koutny@gmail.com>
 */
class ModelAuthToken extends AbstractModelSingle {
    /** @const The first login for setting up a password. */

    const TYPE_INITIAL_LOGIN = 'initial_login';

    /** @const Single sign-on inter-domain ticket */
    const TYPE_SSO = 'sso';

    /** @const Password recovery login */
    const TYPE_RECOVERY = 'recovery';

    /**
     * @return ModelLogin
     */
    public function getLogin() {
        $data = $this->login;
        return ModelLogin::createFromTableRow($data);
    }

}
