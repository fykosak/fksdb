<?php

declare(strict_types=1);

namespace FKSDB\Modules\Core;

use FKSDB\Models\Exceptions\NotImplementedException;
use FKSDB\Models\Utils\FakeStringEnum;
use Fykosak\Utils\Localization\LangMap;
use Nette\Utils\Html;

final class Language extends FakeStringEnum
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
     * @phpstan-return self[]
     */
    public static function cases(): array
    {
        return [
            new self(self::EN),
            new self(self::CS),
        ];
    }

    /**
     * @phpstan-template TValue
     * @phpstan-param array<'cs'|'en',TValue>|LangMap<'cs'|'en',TValue> $map
     * @phpstan-return TValue
     */
    final public function getVariant($map)
    {
        if ($map instanceof LangMap) {
            return $map->get($this->value);//@phpstan-ignore-line
        } else {
            return $map[$this->value];
        }
    }
}
