<?php

namespace Events\Semantics;

use Events\Model\Holder\BaseHolder;
use Nette\Object;

/**
 * Due to author's laziness there's no class doc (or it's self explaining).
 *
 * @author Michal Koutný <michal@fykos.cz>
 */
class Count extends Object {

    use WithEventTrait;

    private $state;

    function __construct($state) {
        $this->state = $state;
    }

    public function __invoke($obj) {
        $baseHolder = $this->getHolder($obj)->getPrimaryHolder();
        $table = $baseHolder->getService()->getTable();
        $table->where($baseHolder->getEventId(), $this->getEvent($obj)->getPrimary());
        $table->where(BaseHolder::STATE_COLUMN, $this->state);
        return $table->count('1');
    }

    public function __toString() {
        return "count({$this->state})";
    }

}
