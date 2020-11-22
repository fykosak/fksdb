<?php

namespace FKSDB\Events\Semantics;

use FKSDB\Events\Model\Holder\BaseHolder;
use Nette\SmartObject;

/**
 * Due to author's laziness there's no class doc (or it's self explaining).
 *
 * @author Michal Koutný <michal@fykos.cz>
 */
class Count {
    use SmartObject;
    use WithEventTrait;

    private string $state;

    public function __construct(string $state) {
        $this->state = $state;
    }

    public function __invoke(...$args): int {
        $baseHolder = $this->getHolder($args[0])->getPrimaryHolder();
        $table = $baseHolder->getService()->getTable();
        $table->where($baseHolder->getEventIdColumn(), $this->getEvent($args[0])->getPrimary());
        $table->where(BaseHolder::STATE_COLUMN, $this->state);
        return $table->count('1');
    }

    public function __toString(): string {
        return "count({$this->state})";
    }

}
