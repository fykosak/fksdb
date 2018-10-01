<?php

use Nette\Database\Table\ActiveRow;

/**
 *
 * @author Michal Koutný <xm.koutny@gmail.com>
 * @property string token
 * @property ActiveRow login
 * @property string data
 * @property string type
 */
class ModelAuthToken extends \AbstractModelSingle {
    /** @const The first login for setting up a password. */
    const TYPE_INITIAL_LOGIN = 'initial_login';

    /** @const Single sign-on inter-domain ticket */
    const TYPE_SSO = 'sso';

    /** @const Password recovery login */
    const TYPE_RECOVERY = 'recovery';

    /** @const Notification about an event application. */
    const TYPE_EVENT_NOTIFY = 'event_notify';

    /**
     * @return \ModelLogin
     */
    public function getLogin() {
        $data = $this->login;
        return \ModelLogin::createFromTableRow($data);
    }

}
