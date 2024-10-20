<?php

declare(strict_types=1);

namespace FKSDB\Models\ORM\Models\Schedule;

use FKSDB\Models\ORM\Columns\Types\EnumColumn;
use Fykosak\Utils\UI\Title;
use Nette\Utils\Html;

enum PersonScheduleState: string implements EnumColumn
{
    case Participated = 'participated';
    case Missed = 'missed';
    case Canceled = 'canceled';
    case Applied = 'applied';

    public function behaviorType(): string
    {
        return match ($this) {
            self::Participated => 'success',
            self::Missed => 'danger',
            self::Canceled => 'secondary',
            default => 'primary',
        };
    }

    public function badge(): Html
    {
        return Html::el('span')
            ->addAttributes(['class' => 'badge bg-' . $this->behaviorType()])
            ->addText($this->label());
    }

    public function label(): string
    {
        switch ($this->value) {
            case self::Participated:
                return _('Participated');
            case self::Missed:
                return _('Missed');
            case self::Canceled:
                return _('Cancelled');
            default:
            case self::Applied:
                return _('Applied');
        }
    }

    public function title(): Title
    {
        return new Title(null, $this->label());
    }
}
