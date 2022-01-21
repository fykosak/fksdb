<?php

namespace FKSDB\Models\Events\Semantics;

use FKSDB\Models\Events\Model\Holder\BaseHolder;
use Nette\SmartObject;

class Count
{
    use SmartObject;
    use WithEventTrait;

    private string $state;

    public function __construct(string $state)
    {
        $this->state = $state;
    }

    public function __invoke(...$args): int
    {
        $baseHolder = $this->getHolder($args[0])->primaryHolder;
        $table = $baseHolder->getService()->getTable();
        $table->where($baseHolder->eventIdColumn, $this->getEvent($args[0])->getPrimary());
        $table->where(BaseHolder::STATE_COLUMN, $this->state);
        return $table->count('1');
    }

    public function __toString(): string
    {
        return "count($this->state)";
    }

}
