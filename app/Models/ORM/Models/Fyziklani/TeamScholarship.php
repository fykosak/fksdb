<?php

declare(strict_types=1);

namespace FKSDB\Models\ORM\Models\Fyziklani;

use FKSDB\Models\ORM\Columns\Types\EnumColumn;
use FKSDB\Models\Utils\FakeStringEnum;
use Nette\Utils\Html;

final class TeamScholarship extends FakeStringEnum implements EnumColumn
{
    // phpcs:disable
    public const None = 'none';
    public const Half = 'half';
    public const Full = 'full';

    // phpcs:enable

    public function badge(): Html
    {
        return Html::el('span')
            ->addAttributes(['class' => 'badge bg-' . $this->getBehaviorType()])
            ->addText($this->label());
    }

    public function getBehaviorType(): string
    {
        switch ($this->value) {
            case self::Full:
                return 'info';
            case self::Half:
                return 'warning';
            case self::None:
                return 'secondary';
        }
        return '';
    }

    public function label(): string
    {
        switch ($this->value) {
            case self::Full:
                return _('Full');
            case self::Half:
                return _('Half');
            case self::None:
                return _('None');
        }
        return '';
    }

    public static function cases(): array
    {
        return [
            new self(self::None),
            new self(self::Half),
            new self(self::Full),
        ];
    }
}
