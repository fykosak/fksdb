<?php

declare(strict_types=1);

namespace FKSDB\Models\ORM\Models;

use FKSDB\Models\ORM\Columns\Types\EnumColumn;
use Fykosak\Utils\UI\Title;
use Nette\Utils\Html;

enum EventParticipantStatus: string implements EnumColumn
{
    case Init = '__init';
    case Applied = 'applied';
    case AutoInvited = 'auto.invited';
    case AutoSpare = 'auto.spare';
    case Cancelled = 'cancelled';
    case Disqualified = 'disqualified';
    case Interested = 'interested';
    case Invited = 'invited';
    case Invited1 = 'invited1';
    case Invited2 = 'invited2';
    case Invited3 = 'invited3';
    case Missed = 'missed';
    //public const OUT_OF_DB = 'out_of_db';
    case OutOfDB = 'outOfDB';
    case Paid = 'paid';
    case Participated = 'participated';
    case Pending = 'pending';
    case Rejected = 'rejected';
    case Spare = 'spare';
    case Spare1 = 'spare1';
    case Spare2 = 'spare2';
    case Spare3 = 'spare3';

    public function badge(): Html
    {
        $badge = '';
        switch ($this) {
            case self::Applied:
            case self::Interested:
            case self::Pending:
                $badge = 'badge bg-color-2';
                break;
            case self::Participated:
                $badge = 'badge bg-color-3';
                break;
            case self::Missed:
                $badge = 'badge bg-color-4';
                break;
            case self::Disqualified:
                $badge = 'badge bg-color-5';
                break;
            case self::Rejected:
            case self::Cancelled:
                $badge = 'badge bg-color-6';
                break;
            case self::Paid:
                $badge = 'badge bg-color-7';
                break;
            case self::OutOfDB:
                $badge = 'badge bg-color-8';
                break;
            case self::Spare:
            case self::Spare1:
            case self::Spare2:
            case self::Spare3:
            case self::AutoSpare:
                $badge = 'badge bg-color-9';
                break;
            case self::Invited:
            case self::Invited1:
            case self::Invited2:
            case self::Invited3:
            case self::AutoInvited:
                $badge = 'badge bg-color-10';
                break;
        }
        return Html::el('span')->addAttributes(['class' => $badge])->addText($this->label());
    }

    public function label(): string
    {
        switch ($this) {
            case self::Applied:
                return _('Applied');
            case self::AutoInvited:
                return _('Auto invited');
            case self::AutoSpare:
                return _('Auto spare');
            case self::Cancelled:
                return _('Cancelled');
            case self::Disqualified:
                return _('Disqualified');
            case self::Interested:
                return _('Interested');
            case self::Invited:
                return _('Invited');
            case self::Invited1:
                return _('Invited 1');
            case self::Invited2:
                return _('Invited 2');
            case self::Invited3:
                return _('Invited 3');
            case self::Missed:
                return _('Missed');
            case self::OutOfDB:
                return _('Out of DB');
            case self::Paid:
                return _('Paid');
            case self::Participated:
                return _('Participated');
            case self::Pending:
                return _('Pending');
            case self::Rejected:
                return _('Rejected');
            case self::Spare:
                return _('Spare');
            case self::Spare1:
                return _('Spare 1');
            case self::Spare2:
                return _('Spare 2');
            case self::Spare3:
                return _('Spare 3');
        }
        return $this->value;
    }

    public function title(): Title
    {
        return new Title(null, $this->label());
    }
}
