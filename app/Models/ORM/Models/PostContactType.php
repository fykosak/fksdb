<?php

declare(strict_types=1);

namespace FKSDB\Models\ORM\Models;

use FKSDB\Models\ORM\Columns\Types\EnumColumn;
use Nette\Utils\Html;

enum PostContactType: string implements EnumColumn
{
    case Delivery = 'D';
    case Permanent = 'P';

    public function badge(): Html
    {
        return Html::el('span')->addAttributes(['class' => 'badge bg-primary'])->addText($this->label());
    }

    public function label(): string
    {
        return match ($this) {
            self::Delivery => _('Delivery'),
            self::Permanent => _('Permanent'),
        };
    }
}
