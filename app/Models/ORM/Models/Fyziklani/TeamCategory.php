<?php

declare(strict_types=1);

namespace FKSDB\Models\ORM\Models\Fyziklani;

// TODO to enum
use FKSDB\Models\ORM\Columns\Types\EnumColumn;
use FKSDB\Models\ORM\Models\EventModel;
use FKSDB\Models\Utils\FakeStringEnum;
use Fykosak\Utils\UI\Title;
use Nette\Utils\Html;

final class TeamCategory extends FakeStringEnum implements EnumColumn
{
    public const A = 'A';
    public const B = 'B';
    public const C = 'C';
    public const O = 'O';
    public const F = 'F';

    /**
     * @phpstan-return self[]
     */
    public static function cases(): array
    {
        return [
            new self(self::A),
            new self(self::B),
            new self(self::C),
            new self(self::F),
            new self(self::O),
        ];
    }

    public function label(): string
    {
        switch ($this->value) {
            case self::A:
                return _('High-school students A');
            case self::B:
                return _('High-school students B');
            case self::C:
                return _('High-school students C');
            case self::F:
                return _('Abroad high-school students');
            case self::O:
                return _('Open');
            default:
                throw new \InvalidArgumentException();
        }
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
                        new self(self::A),
                        new self(self::B),
                        new self(self::C),
                    ];
                }
                return [new self(self::A)];
            case 9:
                if ($event->event_year > 7) {
                    return [
                        new self(self::A),
                        new self(self::B),
                        new self(self::C),
                        new self(self::O),
                    ];
                }
                return [
                    new self(self::A),
                    new self(self::B),
                    new self(self::C),
                    new self(self::O),
                    new self(self::F),
                ];
            case 17:
                return [
                    new self(self::A),
                ];
        }
        return [];
    }

    public function badge(): Html
    {
        // TODO
        return Html::el('span')->addText($this->label());
    }

    /**
     * @internal
     * Protection for applications
     */
    public function __toString(): string
    {
        return $this->value;
    }

    public function title(): Title
    {
        return new Title(null, $this->label());
    }
}
