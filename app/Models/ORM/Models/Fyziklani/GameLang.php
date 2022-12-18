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
        switch ($this->value) {
            case self::CS:
                return _('Czech');
            case self::EN:
                return _('English');
        }
        return ''; // TODO remove on PHP8.1
    }

    /**
     * @return self[]
     */
    public static function cases(): array
    {
        return [
            new static(self::EN),
            new static(self::CS),
        ];
    }
}
