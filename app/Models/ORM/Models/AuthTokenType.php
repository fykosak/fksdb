<?php

declare(strict_types=1);

namespace FKSDB\Models\ORM\Models;

use FKSDB\Models\Exceptions\NotImplementedException;
use FKSDB\Models\ORM\Columns\Types\EnumColumn;
use FKSDB\Models\Utils\FakeStringEnum;
use Fykosak\Utils\UI\Title;
use Nette\Utils\Html;

final class AuthTokenType extends FakeStringEnum implements EnumColumn
{
    public const INITIAL_LOGIN = 'initial_login';
    public const RECOVERY = 'recovery';
    public const EVENT_NOTIFY = 'event_notify';
    public const CHANGE_EMAIL = 'change_email';
    /** @internal */
    public const EMAIL_MESSAGE = 'email_message';
    /** @deprecated */
    public const SSO = 'sso';

    /**
     * @throws NotImplementedException
     */
    public function badge(): Html
    {
        throw new NotImplementedException();
    }

    /**
     * @throws NotImplementedException
     */
    public function label(): string
    {
        throw new NotImplementedException();
    }

    /**
     * @throws NotImplementedException
     */
    public function behaviorType(): string
    {
        throw new NotImplementedException();
    }

    public static function cases(): array
    {
        return [
            new self(self::INITIAL_LOGIN),
            new self(self::RECOVERY),
            new self(self::EVENT_NOTIFY),
            new self(self::CHANGE_EMAIL),
            new self(self::EMAIL_MESSAGE),
            new self(self::SSO),
        ];
    }

    /**
     * @throws NotImplementedException
     */
    public function title(): Title
    {
        return new Title(null, $this->label());
    }
}
