<?php

declare(strict_types=1);

namespace FKSDB\Models\Events\Semantics;

use Nette\SmartObject;

class Parameter
{
    use SmartObject;
    use WithEventTrait;

    private string $parameter;

    /**
     * Parameter constructor.
     * @param string $parameter
     */
    public function __construct(string $parameter)
    {
        $this->parameter = $parameter;
    }

    /**
     * @param ...$args
     * @return mixed
     */
    public function __invoke(...$args)
    {
        return $this->getHolder($args[0])->getParameter($this->parameter);
    }

    public function __toString(): string
    {
        return "param({$this->parameter})";
    }
}
