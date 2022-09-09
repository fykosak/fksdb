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
        return [];
    }
}
