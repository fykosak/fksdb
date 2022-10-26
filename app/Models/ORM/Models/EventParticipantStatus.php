<?php

declare(strict_types=1);

namespace FKSDB\Models\ORM\Models;

use FKSDB\Models\ORM\Columns\Types\EnumColumn;
use FKSDB\Models\Utils\FakeStringEnum;
use Nette\Utils\Html;

class EventParticipantStatus extends FakeStringEnum implements EnumColumn
{

    public const APPLIED = 'applied';
    public const APPLIED_NODSEF = 'applied.nodsef';
    public const APPLIED_NOTSAF = 'applied.notsaf';
    public const APPLIED_TSAF = 'applied.tsaf';
    public const APPROVED = 'approved';
    public const AUTO_INVITED = 'auto.invited';
    public const AUTO_SPARE = 'auto.spare';
    public const CANCELLED = 'cancelled';
    public const DISQUALIFIED = 'disqualified';
    public const INTERESTED = 'interested';
    public const INVITED = 'invited';
    public const INVITED1 = 'invited1';
    public const INVITED2 = 'invited2';
    public const INVITED3 = 'invited3';
    public const MISSED = 'missed';
    public const OUT_OF_DB = 'out_of_db';
    public const PAID = 'paid';
    public const PARTICIPATED = 'participated';
    public const PENDING = 'pending';
    public const REJECTED = 'rejected';
    public const SPARE = 'spare';
    public const SPARE_TSAF = 'spare.tsaf';
    public const SPARE1 = 'spare1';
    public const SPARE2 = 'spare2';
    public const SPARE3 = 'spare3';

    public function badge(): Html
    {
        return Html::el('span')->addText($this->label());
    }

    public function label(): string
    {
        return _($this->value);
    }

    public static function cases(): array
    {
        return [
            new self(self::APPLIED),
            new self(self::APPLIED_NODSEF),
            new self(self::APPLIED_NOTSAF),
            new self(self::APPLIED_TSAF),
            new self(self::APPROVED),
            new self(self::AUTO_INVITED),
            new self(self::AUTO_SPARE),
            new self(self::CANCELLED),
            new self(self::DISQUALIFIED),
            new self(self::INTERESTED),
            new self(self::INVITED),
            new self(self::INVITED1),
            new self(self::INVITED2),
            new self(self::INVITED3),
            new self(self::MISSED),
            new self(self::OUT_OF_DB),
            new self(self::PAID),
            new self(self::PARTICIPATED),
            new self(self::PENDING),
            new self(self::REJECTED),
            new self(self::SPARE),
            new self(self::SPARE_TSAF),
            new self(self::SPARE1),
            new self(self::SPARE2),
            new self(self::SPARE3),
        ];
    }
}
