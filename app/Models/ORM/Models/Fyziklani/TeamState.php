<?php

declare(strict_types=1);

namespace FKSDB\Models\ORM\Models\Fyziklani;

use FKSDB\Models\ORM\Columns\Types\EnumColumn;
use Fykosak\Utils\UI\Title;
use Nette\Utils\Html;

enum TeamState: string implements EnumColumn
{
    case Applied = 'applied';
    case Arrived = 'arrived';
    case Cancelled = 'cancelled';
    case Disqualified = 'disqualified';
    case Missed = 'missed';
    case Participated = 'participated';
    case Pending = 'pending';
    case Spare = 'spare';

    case Init = 'init'; // virtual state for correct ORM

    public function badge(): Html
    {
        return Html::el('span')
            ->addAttributes(['class' => 'badge bg-' . $this->behaviorType()])
            ->addText($this->label());
    }

    public function pseudoState(): self
    {
        return match ($this) {
            self::Pending => self::Applied,
            default => $this,
        };
    }

    public function behaviorType(): string
    {
        return match ($this) {
            self::Arrived => 'danger',
            self::Applied => 'info',
            self::Pending => 'warning',
            self::Spare => 'primary',
            self::Participated => 'success',
            self::Missed, self::Cancelled, self::Init => 'secondary',
            default => 'dark',
        };
    }

    public function label(): string
    {
        return match ($this) {
            self::Arrived => _('Arrived'),
            self::Applied => _('Applied'),
            self::Pending => _('Pending'),
            self::Spare => _('Spare'),
            self::Participated => _('Participated'),
            self::Missed => _('Missed'),
            self::Disqualified => _('Disqualified'),
            self::Cancelled => _('Canceled'),
            default => $this->value,
        };
    }

    /**
     * @return self[]
     */
    public static function possiblyAttendingCases(): array
    {
        return [
            self::Participated,
            self::Spare,
            self::Applied,
            self::Arrived,
        ];
    }

    public function title(): Title
    {
        return new Title(null, $this->label());
    }
}
