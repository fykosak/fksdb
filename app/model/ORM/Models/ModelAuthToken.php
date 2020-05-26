<?php

namespace FKSDB\ORM\Models;

use FKSDB\ORM\AbstractModelSingle;
use Nette\Database\Table\ActiveRow;

/**
 *
 * @author Michal Koutný <xm.koutny@gmail.com>
 * @property-read string token
 * @property-read ActiveRow login
 * @property-read string data
 * @property-read string type
 * @property-read \DateTimeInterface until
 */
class ModelAuthToken extends AbstractModelSingle {
    /** @const The first login for setting up a password. */
    const TYPE_INITIAL_LOGIN = 'initial_login';

    /** @const Single sign-on inter-domain ticket */
    const TYPE_SSO = 'sso';

    /** @const Password recovery login */
    const TYPE_RECOVERY = 'recovery';

    /** @const Notification about an event application. */
    const TYPE_EVENT_NOTIFY = 'event_notify';

    public function getLogin(): ModelLogin {
        $data = $this->login;
        return ModelLogin::createFromActiveRow($data);
    }
}
