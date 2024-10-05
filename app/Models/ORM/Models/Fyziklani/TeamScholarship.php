<?php

declare(strict_types=1);

namespace FKSDB\Models\ORM\Models\Fyziklani;

use FKSDB\Models\ORM\Columns\Types\EnumColumn;
use FKSDB\Models\Utils\FakeStringEnum;
use Fykosak\Utils\UI\Title;
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
            ->addAttributes(['class' => 'badge bg-' . $this->behaviorType()])
            ->addHtml($this->title()->toHtml());
    }

    public function behaviorType(): string
    {
        switch ($this->value) {
            case self::Full:
                return 'info';
            case self::Half:
                return 'warning';
            default:
            case self::None:
                return 'secondary';
        }
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

    public function title(): Title
    {
        return new Title(null, $this->label(), $this->getIconName());
    }

    public function icon(): Html
    {
        return Html::el('span')
            ->addAttributes(['class' => 'badge bg-' . $this->behaviorType()])
            ->addHtml(Html::el('i')->addAttributes(['class' => $this->getIconName()]));
    }

    public function getIconName(): string
    {
        switch ($this->value) {
            case self::Full:
                return 'fas fa-star';
            case self::Half:
                return 'fas fa-star-half-stroke';
            case self::None:
                return 'far fa-star';
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
