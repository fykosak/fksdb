<?php

declare(strict_types=1);

namespace FKSDB\Models\ORM\Models\Fyziklani;

use FKSDB\Models\ORM\Columns\Types\EnumColumn;
use FKSDB\Models\ORM\Models\EventModel;
use Fykosak\Utils\UI\Title;
use Nette\Utils\Html;

enum TeamCategory: string implements EnumColumn
{
    case A = 'A';
    case B = 'B';
    case C = 'C';
    case O = 'O';
    case F = 'F';

    public function label(): string
    {
        return match ($this) {
            self::A => 'A',
            self::B => 'B',
            self::C => 'C',
            self::F => 'F',
            self::O => _('Open'),
        };
    }

    /**
     * @phpstan-return self[]
     */
    public static function casesForEvent(EventModel $event): array
    {
        switch ($event->event_type_id) {
            case 1:
                if ($event->event_year > 6) {
                    return [
                        self::A,
                        self::B,
                        self::C,
                    ];
                }
                return [self::A];
            case 9:
                if ($event->event_year > 7) {
                    return [
                        self::A,
                        self::B,
                        self::C,
                        self::O,
                    ];
                }
                return [
                    self::A,
                    self::B,
                    self::C,
                    self::O,
                    self::F,
                ];
            case 17:
                return [
                    self::A,
                ];
        }
        return [];
    }

    public function behaviorType(): string
    {
        return match ($this) {
            self::A => 'danger',
            self::B => 'warning',
            self::C => 'success',
            self::O => 'primary',
            default => 'dark',
        };
    }

    public function badge(): Html
    {
        return Html::el('span')
            ->addAttributes(['class' => 'badge bg-' . $this->behaviorType()])
            ->addText($this->label());
    }

    public function title(): Title
    {
        return new Title(null, $this->label());
    }
}
