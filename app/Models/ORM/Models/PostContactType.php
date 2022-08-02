<?php

declare(strict_types=1);

namespace FKSDB\Models\ORM\Models;

use FKSDB\Models\ORM\Columns\Types\EnumColumn;
use FKSDB\Models\Utils\FakeStringEnum;
use Nette\Utils\Html;

class PostContactType extends FakeStringEnum implements EnumColumn
{
    public const DELIVERY = 'D';
    public const PERMANENT = 'P';

    public function badge(): Html
    {
        return Html::el('span')->addAttributes(['class' => 'badge bg-primary'])->addText($this->label());
    }

    public function label(): string
    {
        switch ($this->value) {
            default:
            case self::DELIVERY:
                return _('Delivery');
            case self::PERMANENT:
                return _('Permanent');
        }
    }

    public static function cases(): array
    {
        return [
            new static(self::PERMANENT),
            new static(self::DELIVERY),
        ];
    }
}
