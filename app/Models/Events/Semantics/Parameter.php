<?php

declare(strict_types=1);

namespace FKSDB\Models\Events\Semantics;

use FKSDB\Models\Transitions\Holder\ParticipantHolder;
use FKSDB\Models\Transitions\Statement;
use Nette\SmartObject;

/**
 * @implements Statement<mixed,ParticipantHolder>
 */
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
        /** @var ParticipantHolder $holder */
        [$holder] = $args;
        return $holder->getModel()->event->getParameter($this->parameter);
    }
}
