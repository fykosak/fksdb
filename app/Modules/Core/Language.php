<?php

declare(strict_types=1);

namespace FKSDB\Modules\Core;

use Nette\Utils\Html;

enum Language: string
{
    case Cs = 'cs';
    case En = 'en';

    /**
     * @throws NotImplementedException
     */
    public function badge(): Html
    {
        return Html::el('span')->addAttributes(['class' => 'badge bg-primary'])->addText($this->label());
    }

    /**
     * @throws NotImplementedException
     */
    public function label(): string
    {
        return match ($this) {
            self::Cs => _('Czech'),
            self::En => _('English'),
        };
    }

    /**
     * @throws NotImplementedException
     */
    public function locales(): string
    {
        return match ($this) {
            self::Cs => 'cs_CZ.utf-8',
            self::En => 'en_US.utf-8',
        };
    }
}
