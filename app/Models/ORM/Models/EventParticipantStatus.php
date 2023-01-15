<?php

declare(strict_types=1);

namespace FKSDB\Models\ORM\Models;

use FKSDB\Models\Exceptions\NotImplementedException;
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

    public function badge(): Html
    {
        $badge = '';
        switch ($this->value) {
            case self::APPLIED:
            case self::APPLIED_NODSEF:
            case self::APPLIED_NOTSAF:
            case self::APPLIED_TSAF:
            case self::APPROVED:
                $badge = 'badge bg-color-1';
                break;
            case self::INTERESTED:
            case self::PENDING:
                $badge = 'badge bg-color-2';
                break;
            case self::PARTICIPATED:
                $badge = 'badge bg-color-3';
                break;
            case self::MISSED:
                $badge = 'badge bg-color-4';
                break;
            case self::DISQUALIFIED:
                $badge = 'badge bg-color-5';
                break;
            case self::REJECTED:
            case self::CANCELLED:
                $badge = 'badge bg-color-6';
                break;
            case self::PAID:
                $badge = 'badge bg-color-7';
                break;
            case self::OUT_OF_DB:
                $badge = 'badge bg-color-8';
                break;
            case self::SPARE:
            case self::SPARE1:
            case self::SPARE2:
            case self::SPARE3:
            case self::SPARE_TSAF:
            case self::AUTO_SPARE:
                $badge = 'badge bg-color-9';
                break;
            case self::INVITED:
            case self::INVITED1:
            case self::INVITED2:
            case self::INVITED3:
            case self::AUTO_INVITED:
                $badge = 'badge bg-color-10';
                break;
        }
        return Html::el('span')->addAttributes(['class' => $badge])->addText($this->label());
    }

    public function label(): string
    {
        switch ($this->value) {
            case self::APPLIED:
                return _('Applied');
            case self::APPLIED_NODSEF:
                return _('Applied no DSEF');
            case self::APPLIED_NOTSAF:
                return _('Applied no TSAF');
            case self::APPLIED_TSAF:
                return _('Applied TSAF');
            case self::APPROVED:
                return _('Approved');
            case self::AUTO_INVITED:
                return _('Auto invited');
            case self::AUTO_SPARE:
                return _('Auto spare');
            case self::CANCELLED:
                return _('Cancelled');
            case self::DISQUALIFIED:
                return _('Disqualified');
            case self::INTERESTED:
                return _('Interested');
            case self::INVITED:
                return _('Invited');
            case self::INVITED1:
                return _('Invited 1');
            case self::INVITED2:
                return _('Invited 2');
            case self::INVITED3:
                return _('Invited 3');
            case self::MISSED:
                return _('Missed');
            case self::OUT_OF_DB:
                return _('Out of DB');
            case self::PAID:
                return _('Paid');
            case self::PARTICIPATED:
                return _('Participated');
            case self::PENDING:
                return _('Pending');
            case self::REJECTED:
                return _('Rejected');
            case self::SPARE:
                return _('Spare');
            case self::SPARE_TSAF:
                return _('Spare TSAF');
            case self::SPARE1:
                return _('Spare 1');
            case self::SPARE2:
                return _('Spare 2');
            case self::SPARE3:
                return _('Spare 3');
        }
        return $this->value;
    }
}
