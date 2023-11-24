<?php

declare(strict_types=1);

namespace FKSDB\Models\ORM\Models\Schedule;

use FKSDB\Models\ORM\Columns\Types\EnumColumn;
use FKSDB\Models\Utils\FakeStringEnum;
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

    public function badge(): Html
    {
        switch ($this->value) {
            case self::Participated:
                return Html::el('span')->addAttributes(['class' => 'badge bg-success'])->addText($this->label());
            case self::Missed:
                return Html::el('span')->addAttributes(['class' => 'badge bg-danger'])->addText($this->label());
            case self::Cancelled:
                return Html::el('span')->addAttributes(['class' => 'badge bg-secondary'])->addText($this->label());
            case self::Applied:
                return Html::el('span')->addAttributes(['class' => 'badge bg-primary'])->addText($this->label());
        }
        return Html::el('span')->addText($this->label());
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
            case self::Applied:
                return _('Applied');
        }
        return '';
    }
}
