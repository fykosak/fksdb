<?php

declare(strict_types=1);

namespace FKSDB\Models\Events\Semantics;

use FKSDB\Models\Events\Model\Holder\BaseHolder;
use FKSDB\Models\Transitions\Holder\ModelHolder;
use Nette\SmartObject;

class Parameter
{
    use SmartObject;

    private string $parameter;

    public function __construct(string $parameter)
    {
        $this->parameter = $parameter;
    }

    /**
     * @param BaseHolder $holder
     * @return mixed
     */
    public function __invoke(ModelHolder $holder)
    {
        return $holder->getParameter($this->parameter);
    }

    public function __toString(): string
    {
        return "param($this->parameter)";
    }
}
