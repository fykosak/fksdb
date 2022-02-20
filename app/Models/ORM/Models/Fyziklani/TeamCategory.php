<?php

declare(strict_types=1);

namespace FKSDB\Models\ORM\Models\Fyziklani;

// TODO to enum
class TeamCategory
{
    public const CATEGORY_A = 'A';
    public const CATEGORY_B = 'B';
    public const CATEGORY_C = 'C';
    public const CATEGORY_O = 'O';
    public const CATEGORY_F = 'F';

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
            new self(self::CATEGORY_A),
            new self(self::CATEGORY_B),
            new self(self::CATEGORY_C),
            new self(self::CATEGORY_F),
            new self(self::CATEGORY_O),
        ];
    }

    public function getName(): string
    {
        switch ($this->value) {
            case self::CATEGORY_A:
                return _('High-school students A');
            case self::CATEGORY_B:
                return _('High-school students B');
            case self::CATEGORY_C:
                return _('High-school students C');
            case self::CATEGORY_F:
                return _('Abroad high-school students');
            case self::CATEGORY_O:
                return _('Open');
            default:
                throw new \InvalidArgumentException();
        }
    }
}
