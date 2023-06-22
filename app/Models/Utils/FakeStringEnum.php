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
     * @return static|null
     */
    final public static function tryFrom(?string $value): ?self
    {
        if (is_null($value)) {
            return null;
        }
        return new static($value);
    }

    /**
     * @return static[]
     */
    abstract public static function cases(): array;

    public function __toString(): string
    {
        return $this->value;
    }
}
