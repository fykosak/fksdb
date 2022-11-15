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

    /**
     * @throws NotImplementedException
     */
    public function badge(): Html
    {
        return Html::el('span')->addAttributes(['class' => $this->getBehaviorType()])->addText($this->label());
    }

    /**
     * @throws NotImplementedException
     */
    public function label(): string
    {
        return match ($this) {
            self::Applied => _('applied'),
            self::Pending => _('pending'),
            self::Approved => _('approved'),
            self::Spare => _('spare'),
            self::Participated => _('participated'),
            self::Missed => _('missed'),
            self::Disqualified => _('disqualified'),
            self::Cancelled => _('canceled'),
            default => throw new NotImplementedException(),
        };
    }

    public function getBehaviorType(): string
    {
        return match ($this) {
            self::Applied => 'badge bg-color-1',
            self::Pending => 'badge bg-color-2',
            self::Approved => 'badge bg-color-7',
            self::Spare => 'badge bg-color-9',
            self::Participated => 'badge bg-color-3',
            self::Missed => 'badge bg-color-4',
            self::Disqualified => 'badge bg-color-5',
            self::Cancelled, self::Init => 'badge bg-color-6',
        };
    }
}
