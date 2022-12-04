<?php

declare(strict_types=1);

namespace FKSDB\Models\ORM\Models\Fyziklani;

// TODO to enum
use FKSDB\Models\Exceptions\NotImplementedException;
use FKSDB\Models\ORM\Columns\Types\EnumColumn;
use FKSDB\Models\ORM\Models\EventModel;
use FKSDB\Models\Utils\FakeStringEnum;
use Nette\Utils\Html;

class TeamCategory extends FakeStringEnum implements EnumColumn
{
    public const A = 'A';
    public const B = 'B';
    public const C = 'C';
    public const O = 'O';
    public const F = 'F';

    /**
     * @return self[]
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
     * @return self[]
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
        }
        return [];
    }

    public function badge(): Html
    {
        return Html::el('span')
            ->addAttributes(['class' => 'badge bg-' . $this->getBehaviorType()])
            ->addText($this->label());
    }

    /**
     * @internal
     * Protection for applications
     */
    public function __toString(): string
    {
        return $this->value;
    }

    /**
     * @throws NotImplementedException
     */
    public function getBehaviorType(): string
    {
        switch ($this->value) {
            case self::A:
                return 'color-4';
            case self::B:
                return 'color-2';
            case self::C:
                return 'color-3';
            case self::F:
                return 'color-5';
            case self::O:
                return 'color-10';
        }
        throw new NotImplementedException();
    }
}
