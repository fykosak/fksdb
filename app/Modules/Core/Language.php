<?php

declare(strict_types=1);

namespace FKSDB\Modules\Core;

use FKSDB\Models\Exceptions\NotImplementedException;
use FKSDB\Models\Utils\FakeStringEnum;
use Nette\Utils\Html;

class Language extends FakeStringEnum
{
    public const CS = 'cs';
    public const EN = 'en';

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
        switch ($this->value) {
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
        switch ($this->value) {
            case self::CS:
                return 'cs_CZ.utf-8';
            case self::EN:
                return 'en_US.utf-8';
        }
        throw new NotImplementedException();
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
