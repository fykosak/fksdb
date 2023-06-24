<?php

declare(strict_types=1);

namespace FKSDB\Models\Events\Semantics;

use FKSDB\Models\Events\Model\Holder\BaseHolder;
use FKSDB\Models\Transitions\Statement;
use Nette\SmartObject;

class Parameter implements Statement
{
    use SmartObject;

    private string $parameter;

    public function __construct(string $parameter)
    {
        $this->parameter = $parameter;
    }

    /**
     * @return mixed
     */
    public function __invoke(...$args)
    {
        /** @var BaseHolder $holder */
        [$holder] = $args;
        return $holder->event->getParameter($this->parameter);
    }
}
