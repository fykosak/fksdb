<?php

declare(strict_types=1);

namespace FKSDB\Models\ORM\Models\Fyziklani;

use FKSDB\Models\ORM\Columns\Types\EnumColumn;
use FKSDB\Models\Utils\FakeStringEnum;
use Nette\Utils\Html;

class GameLang extends FakeStringEnum implements EnumColumn
{
    public const CS = 'cs';
    public const EN = 'en';

    public function badge(): Html
    {
        return Html::el('span')->addAttributes(['class' => 'badge bg-primary'])->addText($this->label());
    }

    public function label(): string
    {
        return match ($this->value) {
            self::CS => _('Czech'),
            self::EN => _('English'),
            default => '',
        };
    }

    public static function cases(): array
    {
        return [
            new static(self::EN),
            new static(self::CS),
        ];
    }

    public function getBehaviorType(): string
    {
        return 'badge bg-primary';
    }
}
