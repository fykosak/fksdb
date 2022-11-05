<?php

declare(strict_types=1);

namespace FKSDB\Models\ORM\Models;

use FKSDB\Models\Exceptions\NotImplementedException;
use FKSDB\Models\ORM\Columns\Types\EnumColumn;
use Nette\Utils\Html;

enum AuthTokenType: string implements EnumColumn
{
    case InitialLogin = 'initial_login';
    case Recovery = 'recovery';
    case EventNotify = 'event_notify';
    case ChangeEmail = 'change_email';
    /** @internal */
    case EmailMessage = 'email_message';
    /** @deprecated */
    case SSO = 'sso';

    public function badge(): Html
    {
        throw new NotImplementedException();
    }

    public function label(): string
    {
        throw new NotImplementedException();
    }
}
