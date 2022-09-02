<?php

declare(strict_types=1);

namespace FKSDB\Models\Events\Semantics;

use FKSDB\Models\Events\Model\Holder\BaseHolder;
use FKSDB\Models\Transitions\Holder\ModelHolder;
use Nette\SmartObject;

class Count
{
    use SmartObject;

    private string $state;

    public function __construct(string $state)
    {
        $this->state = $state;
    }

    /**
     * @param BaseHolder $holder
     */
    public function __invoke(ModelHolder $holder): int
    {
        $table = $holder->service->getTable();
        $table->where('event_participant.event_id', $holder->event->getPrimary());
        $table->where(BaseHolder::STATE_COLUMN, $this->state);
        return $table->count('1');
    }

    public function __toString(): string
    {
        return "count($this->state)";
    }
}
