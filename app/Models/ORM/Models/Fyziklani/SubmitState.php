<?php

declare(strict_types=1);

namespace FKSDB\Models\ORM\Models\Fyziklani;

use FKSDB\Models\ORM\Columns\Types\EnumColumn;
use FKSDB\Models\Utils\FakeStringEnum;
use Fykosak\Utils\UI\Title;
use Nette\Utils\Html;

final class SubmitState extends FakeStringEnum implements EnumColumn
{
    // phpcs:disable
    public const NotChecked = 'not_checked';
    public const Checked = 'checked';
    // phpcs:enable

    public function behaviorType(): string
    {
        switch ($this->value) {
            case self::Checked:
                return 'success';
            default:
            case self::NotChecked:
                return 'danger';
        }
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
            case self::Checked:
                return 'checked';
            default:
            case self::NotChecked:
                return 'not checked';
        }
    }

    public static function cases(): array
    {
        return [
            new self(self::NotChecked),
            new self(self::Checked),
        ];
    }

    public function title(): Title
    {
        return new Title(null, $this->label());
    }
}
