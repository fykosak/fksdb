<?php

declare(strict_types=1);

namespace FKSDB\Models\ORM\Models\Schedule;

use FKSDB\Models\ORM\Columns\Types\EnumColumn;
use FKSDB\Models\Utils\FakeStringEnum;
use Fykosak\Utils\UI\Title;
use Nette\Utils\Html;

final class PersonScheduleState extends FakeStringEnum implements EnumColumn
{
    // phpcs:disable
    public const Participated = 'participated';
    public const Missed = 'missed';
    public const Cancelled = 'cancelled';
    public const Applied = 'applied';

    // phpcs:enable
    public static function cases(): array
    {
        return [
            new self(self::Participated),
            new self(self::Missed),
            new self(self::Cancelled),
            new self(self::Applied),
        ];
    }

    public function getBehaviorType(): string
    {
        switch ($this->value) {
            case self::Participated:
                return 'success';
            case self::Missed:
                return 'danger';
            case self::Cancelled:
                return 'secondary';
            case self::Applied:
            default:
                return 'primary';
        }
    }

    public function badge(): Html
    {
        return Html::el('span')
            ->addAttributes(['class' => 'badge bg-' . $this->getBehaviorType()])
            ->addText($this->label());
    }

    public function label(): string
    {
        switch ($this->value) {
            case self::Participated:
                return _('Participated');
            case self::Missed:
                return _('Missed');
            case self::Cancelled:
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
