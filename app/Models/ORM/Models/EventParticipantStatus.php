<?php

declare(strict_types=1);

namespace FKSDB\Models\ORM\Models;

use FKSDB\Models\ORM\Columns\Types\EnumColumn;
use FKSDB\Models\Utils\FakeStringEnum;
use Nette\Utils\Html;

class EventParticipantStatus extends FakeStringEnum implements EnumColumn
{

    public const APPLIED = 'APPLIED';
    public const APPLIED_NODSEF = 'APPLIED.NODSEF';
    public const APPLIED_NOTSAF = 'APPLIED.NOTSAF';
    public const APPLIED_TSAF = 'APPLIED.TSAF';
    public const APPROVED = 'APPROVED';
    public const AUTO_INVITED = 'AUTO.INVITED';
    public const AUTO_SPARE = 'AUTO.SPARE';
    public const CANCELLED = 'CANCELLED';
    public const DISQUALIFIED = 'DISQUALIFIED';
    public const INTERESTED = 'INTERESTED';
    public const INVITED = 'INVITED';
    public const INVITED1 = 'INVITED1';
    public const INVITED2 = 'INVITED2';
    public const INVITED3 = 'INVITED3';
    public const MISSED = 'MISSED';
    public const OUT_OF_DB = 'OUT_OF_DB';
    public const PAID = 'PAID';
    public const PARTICIPATED = 'PARTICIPATED';
    public const PENDING = 'PENDING';
    public const REJECTED = 'REJECTED';
    public const SPARE = 'SPARE';
    public const SPARE_TSAF = 'SPARE.TSAF';
    public const SPARE1 = 'SPARE1';
    public const SPARE2 = 'SPARE2';
    public const SPARE3 = 'SPARE3';

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
