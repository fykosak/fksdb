<?php

declare(strict_types=1);

namespace FKSDB\Models\ORM\Models\Fyziklani;

// TODO to enum
use FKSDB\Models\ORM\Models\ModelEvent;
use Nette\Utils\Html;

class TeamCategory
{
    public const A = 'A';
    public const B = 'B';
    public const C = 'C';
    public const O = 'O';
    public const F = 'F';

    public string $value;

    public function __construct(string $category)
    {
        $this->value = $category;
    }

    public static function tryFrom(?string $category): ?self
    {
        return $category ? new self($category) : null;
    }

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

    public function getName(): string
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
    public static function casesForEvent(ModelEvent $event): array
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
        // TODO
        return Html::el('span');
    }

    /**
     * @internal
     * Protection for applications
     */
    public function __toString(): string
    {
        return $this->value;
    }
}
