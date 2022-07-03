<?php

declare(strict_types=1);

namespace FKSDB\Models\ORM\Models\Fyziklani;

use FKSDB\Models\ORM\Columns\Types\EnumColumn;
use Nette\Utils\Html;

class GameLang implements EnumColumn
{
    public const CS = 'cs';
    public const EN = 'en';

    public string $value;

    public function __construct(string $lang)
    {
        $this->value = $lang;
    }

    public static function tryFrom(?string $lang): ?self
    {
        if (is_null($lang)) {
            return null;
        }
        return new self($lang);
    }

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
}
