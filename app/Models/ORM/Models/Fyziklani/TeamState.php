<?php

declare(strict_types=1);

namespace FKSDB\Models\ORM\Models\Fyziklani;

use FKSDB\Models\Exceptions\NotImplementedException;
use FKSDB\Models\ORM\Columns\Types\EnumColumn;
use Nette\Utils\Html;

enum TeamState: string implements EnumColumn
{
    case Applied = 'applied';
    case Pending = 'pending';
    case Approved = 'approved';
    case Spare = 'spare';
    case Participated = 'participated';
    case Missed = 'missed';
    case Disqualified = 'disqualified';
    case Cancelled = 'cancelled';
    case Init = 'init'; // virtual state for correct ORM

    public function badge(): Html
    {
        return Html::el('span')
            ->addAttributes(['class' => 'badge bg-' . $this->getBehaviorType()])
            ->addText($this->label());
    }

    public function label(): string
    {
        return match ($this) {
            self::Applied => _('Applied'),
            self::Pending => _('Pending'),
            self::Approved => _('Approved'),
            self::Spare => _('Spare'),
            self::Participated => _('Participated'),
            self::Missed => _('Missed'),
            self::Disqualified => _('Disqualified'),
            self::Cancelled => _('Canceled'),
            self::Init => _('Init'),
        };
    }

    public function getBehaviorType(): string
    {
        return match ($this) {
            self::Applied, self::Approved => 'info',
            self::Pending => 'warning',
            self::Spare => 'primary',
            self::Participated => 'success',
            self::Missed, self::Cancelled, self::Init => 'secondary',
            self::Disqualified => 'danger',
        };
    }
}
