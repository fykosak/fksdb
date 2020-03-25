<?php

namespace Events\Semantics;

use Events\Model\Holder\BaseHolder;
use FKSDB\Expressions\EvaluatedExpression;
use Nette\Application\BadRequestException;
use Nette\SmartObject;

/**
 * Due to author's laziness there's no class doc (or it's self explaining).
 *
 * @author Michal Koutný <michal@fykos.cz>
 */
class Count extends EvaluatedExpression {
    use SmartObject;
    use WithEventTrait;

    private $state;

    /**
     * Count constructor.
     * @param $state
     */
    function __construct($state) {
        $this->state = $state;
    }

    /**
     * @param array $args
     * @return bool
     * @throws BadRequestException
     */
    public function __invoke(...$args): bool {
        $baseHolder = $this->getHolder($args[0])->getPrimaryHolder();
        $table = $baseHolder->getService()->getTable();
        $table->where($baseHolder->getEventId(), $this->getEvent($args[0])->getPrimary());
        $table->where(BaseHolder::STATE_COLUMN, $this->state);
        return $table->count('1');
    }

    /**
     * @return string
     */
    public function __toString() {
        return "count({$this->state})";
    }

}
