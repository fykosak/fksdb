<?php

declare(strict_types=1);

namespace FKSDB\Models;

use Nette\InvalidStateException;

class LocalizedString implements \ArrayAccess
{
    private array $variants;

    public function __construct(array $variants)
    {
        $this->variants = $variants;
    }

    public function __get(string $lang): ?string
    {
        return $this->variants[$lang] ?? null;
    }

    /**
     * @param string $offset
     */
    public function offsetExists($offset): bool
    {
        return isset($this->variants[$offset]);
    }

    /**
     * @param string $offset
     */
    public function offsetGet($offset): ?string
    {
        return $this->variants[$offset] ?? null;
    }

    /**
     * @param string $offset
     * @param string $value
     */
    public function offsetSet($offset, $value): void
    {
        throw new InvalidStateException('Can not set variant');
    }

    /**
     * @param string $offset
     */
    public function offsetUnset($offset): void
    {
        throw new InvalidStateException('Can not delete variant');
    }

    public function __serialize(): array
    {
        return $this->variants;
    }
}
