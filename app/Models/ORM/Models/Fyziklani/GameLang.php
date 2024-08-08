<?php

declare(strict_types=1);

namespace FKSDB\Models\ORM\Models\Fyziklani;

use FKSDB\Models\ORM\Columns\Types\EnumColumn;
use FKSDB\Models\Utils\FakeStringEnum;
use FKSDB\Modules\Core\Language;
use Fykosak\Utils\UI\Title;
use Nette\InvalidStateException;
use Nette\Utils\Html;

final class GameLang extends FakeStringEnum implements EnumColumn
{
    public const CS = 'cs';
    public const EN = 'en';

    public function badge(): Html
    {
        return Html::el('span')->addAttributes(['class' => 'badge bg-' . $this->behaviorType()])->addText(
            $this->label()
        );
    }

    public function behaviorType(): string
    {
        switch ($this->value) {
            case self::CS:
                return 'primary';
            case self::EN:
                return 'danger';
        }
        throw new InvalidStateException();
    }

    public function label(): string
    {
        switch ($this->value) {
            case self::CS:
                return _('Czech');
            case self::EN:
                return _('English');
        }
        throw new InvalidStateException();
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

    public function title(): Title
    {
        return new Title(null, $this->label());
    }

    public function toLanguage(): Language
    {
        switch ($this->value) {
            case self::CS:
                return Language::from(Language::CS);
            case self::EN:
                return Language::from(Language::EN);
        }
        throw new InvalidStateException();
    }
}
