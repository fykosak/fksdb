<?php

declare(strict_types=1);

namespace FKSDB\Models\Utils;

abstract class FakeStringEnum
{
    public string $value;

    final protected function __construct(string $value)
    {
        $this->value = $value;
    }

    /**
     * @phpstan-return static|null
     */
    final public static function tryFrom(?string $value): ?self
    {
        if (is_null($value)) {
            return null;
        }
        try {
            return self::from($value);
        } catch (\InvalidArgumentException$exception) {
            return null;
        }
    }

    /**
     * @phpstan-return static
     */
    final public static function from(string $value): self
    {
        foreach (static::cases() as $case) {
            if ($value === $case->value) {
                return $case;
            }
        }
        throw new \InvalidArgumentException();
    }

    /**
     * @phpstan-return static[]
     */
    abstract public static function cases(): array;

    public function __toString(): string
    {
        return $this->value;
    }
}
