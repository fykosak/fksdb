<?php

namespace FKSDB\Models\ORM\Models;

use Fykosak\NetteORM\AbstractModel;
use Nette\Database\Table\ActiveRow;

/**
 * @property-read string token
 * @property-read ActiveRow login
 * @property-read int login_id
 * @property-read string data
 * @property-read string type
 * @property-read \DateTimeInterface until
 */
class ModelAuthToken extends AbstractModel
{

    /** @const The first login for setting up a password. */
    public const TYPE_INITIAL_LOGIN = 'initial_login';
    /** @const Password recovery login */
    public const TYPE_RECOVERY = 'recovery';
    /** @const Notification about an event application. */
    public const TYPE_EVENT_NOTIFY = 'event_notify';

    public function getLogin(): ModelLogin
    {
        return ModelLogin::createFromActiveRow($this->login);
    }
}
