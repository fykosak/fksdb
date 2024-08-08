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
    // phpcs:disable
    public const InitialLogin = 'initial_login';
    public const Recovery = 'recovery';
    public const EventNotify = 'event_notify';
    public const ChangeEmail = 'change_email';
    public const Unsubscribe = 'unsubscribe';
    /** @internal */
    public const EmailMessage = 'email_message';
    /** @deprecated */
    public const SSO = 'sso';
    // phpcs:enable
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
            new self(self::InitialLogin),
            new self(self::Recovery),
            new self(self::EventNotify),
            new self(self::ChangeEmail),
            new self(self::EmailMessage),
            new self(self::Unsubscribe),
            new self(self::SSO),
        ];
    }

    public function refresh(): bool
    {
        switch ($this->value) {
            case self::Recovery:
            case self::ChangeEmail:
            case self::EmailMessage:
            case self::SSO:
            case self::InitialLogin:
            default:
                return false;
            case self::EventNotify:
            case self::Unsubscribe:
                return true;
        }
    }

    /**
     * @throws NotImplementedException
     */
    public function title(): Title
    {
        return new Title(null, $this->label());
    }
}
