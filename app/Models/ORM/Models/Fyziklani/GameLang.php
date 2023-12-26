<?php

declare(strict_types=1);

namespace FKSDB\Models\ORM\Models\Fyziklani;

use FKSDB\Models\ORM\Columns\Types\EnumColumn;
use FKSDB\Models\Utils\FakeStringEnum;
use Fykosak\Utils\UI\Title;
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
        return ''; // TODO remove on PHP8.1
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
}
