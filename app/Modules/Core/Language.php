<?php

declare(strict_types=1);

namespace FKSDB\Modules\Core;

use FKSDB\Models\Exceptions\NotImplementedException;
use Nette\Utils\Html;

enum Language: string
{
    case CS = 'cs';
    case EN = 'en';

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
        switch ($this) {
            case self::CS:
                return _('Czech');
            case self::EN:
                return _('English');
        }
        throw new NotImplementedException();
    }

    /**
     * @throws NotImplementedException
     */
    public function locales(): string
    {
        switch ($this) {
            case self::CS:
                return 'cs_CZ.utf-8';
            case self::EN:
                return 'en_US.utf-8';
        }
        throw new NotImplementedException();
    }
}
