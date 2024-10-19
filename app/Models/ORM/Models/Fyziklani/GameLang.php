<?php

declare(strict_types=1);

namespace FKSDB\Models\ORM\Models\Fyziklani;

use FKSDB\Models\ORM\Columns\Types\EnumColumn;
use Fykosak\Utils\UI\Title;
use Nette\Utils\Html;

enum GameLang: string implements EnumColumn
{
    case CS = 'cs';
    case EN = 'en';

    public function badge(): Html
    {
        return Html::el('span')->addAttributes(['class' => 'badge bg-' . $this->behaviorType()])->addText(
            $this->label()
        );
    }

    public function behaviorType(): string
    {
        return match ($this) {
            self::CS => 'primary',
            self::EN => 'danger',
        };
    }

    public function label(): string
    {
        return match ($this) {
            self::CS => _('Czech'),
            self::EN => _('English'),
        };
    }

    public function title(): Title
    {
        return new Title(null, $this->label());
    }
}
