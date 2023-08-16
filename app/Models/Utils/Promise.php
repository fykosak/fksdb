<?php

declare(strict_types=1);

namespace FKSDB\Models\Utils;

use Nette\SmartObject;

/**
 * Pseudopromise where we want to evaluate a value (provided as callback)
 * later than promise creation.
 * @template TReturn
 */
class Promise
{
    use SmartObject;

    /** @var callable():(TReturn|null) */
    private $callback;

    private bool $called = false;
    /** @var TReturn|null */
    private $value;

    /**
     * @phpstan-param callable():(TReturn|null) $callback
     */
    public function __construct(callable $callback)
    {
        $this->callback = $callback;
    }

    /**
     * @phpstan-return TReturn|null
     */
    public function getValue()
    {
        if (!$this->called) {
            $this->value = ($this->callback)();
            $this->called = true;
        }
        return $this->value;
    }
}
