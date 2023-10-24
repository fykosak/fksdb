<?php

declare(strict_types=1);

namespace FKSDB\Models\ORM\Models\Schedule;

use FKSDB\Models\ORM\Columns\Types\EnumColumn;
use FKSDB\Models\Utils\FakeStringEnum;
use Nette\Utils\Html;

final class PersonScheduleState extends FakeStringEnum implements EnumColumn
{
    public const PARTICIPATED = 'participated';
    public const MISSED = 'missed';

    public static function cases(): array
    {
        return [
            new self(self::PARTICIPATED),
            new self(self::MISSED),
        ];
    }

    public function badge(): Html
    {
        switch ($this->value) {
            case self::PARTICIPATED:
                return Html::el('span')->addAttributes(['class' => 'badge bg-success'])->addText($this->label());
            case self::MISSED:
                return Html::el('span')->addAttributes(['class' => 'badge bg-danger'])->addText($this->label());
        }
        return Html::el('span')->addText($this->label());
    }

    public function label(): string
    {
        switch ($this->value) {
            case self::PARTICIPATED:
                return _('Participated');
            case self::MISSED:
                return _('Missed');
        }
        return '';
    }
}
