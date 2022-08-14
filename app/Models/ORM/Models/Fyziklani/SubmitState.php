<?php

declare(strict_types=1);

namespace FKSDB\Models\ORM\Models\Fyziklani;

use FKSDB\Models\ORM\Columns\Types\EnumColumn;
use FKSDB\Models\Utils\FakeStringEnum;
use Nette\Utils\Html;

class SubmitState extends FakeStringEnum implements EnumColumn
{
    public const NOT_CHECKED = 'not_checked';
    public const CHECKED = 'checked';

    public function badge(): Html
    {
        switch ($this->value) {
            case self::CHECKED:
                return Html::el('span')
                    ->addAttributes(['class' => 'badge bg-success'])
                    ->addText($this->label());
            default:
            case self::NOT_CHECKED:
                return Html::el('span')
                    ->addAttributes(['class' => 'badge bg-danger'])
                    ->addText($this->label());
        }
    }

    public function label(): string
    {
        switch ($this->value) {
            case self::CHECKED:
                return 'checked';
            default:
            case self::NOT_CHECKED:
                return 'not checked';
        }
    }

    public static function cases(): array
    {
        return [
            new self(self::NOT_CHECKED),
            new self(self::CHECKED),
        ];
    }
}
