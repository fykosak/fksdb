<?php

declare(strict_types=1);

namespace FKSDB\Models\ORM\Models\Fyziklani;

use FKSDB\Models\ORM\Columns\Types\EnumColumn;
use Fykosak\Utils\UI\Title;
use Nette\Utils\Html;

enum TeamScholarship: string implements EnumColumn
{
    case None = 'none';
    case Half = 'half';
    case Full = 'full';

    public function badge(): Html
    {
        return Html::el('span')
            ->addAttributes(['class' => 'badge bg-' . $this->behaviorType()])
            ->addHtml($this->title()->toHtml());
    }

    public function behaviorType(): string
    {
        return match ($this) {
            self::Full => 'info',
            self::Half => 'warning',
            self::None => 'secondary',
        };
    }

    public function label(): string
    {
        return match ($this) {
            self::Full => _('Full'),
            self::Half => _('Half'),
            self::None => _('None'),
        };
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
        return match ($this) {
            self::Full => 'fas fa-star',
            self::Half => 'fas fa-star-half-stroke',
            self::None => 'far fa-star',
        };
    }
}
