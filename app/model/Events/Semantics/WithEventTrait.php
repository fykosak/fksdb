<?php

namespace FKSDB\Events\Semantics;

use FKSDB\Events\Model\Holder\BaseHolder;
use FKSDB\Events\Model\Holder\Field;
use FKSDB\Events\Model\Holder\Holder;
use FKSDB\ORM\Models\ModelEvent;

/**
 * Due to author's laziness there's no class doc (or it's self explaining).
 *
 * @author Michal Koutný <michal@fykos.cz>
 */
trait WithEventTrait {

    /**
     * @param mixed $obj
     * @return ModelEvent
     * @throws \InvalidArgumentException
     */
    protected function getEvent($obj): ModelEvent {
        return ($holder = $this->getHolder($obj)) ? $holder->getPrimaryHolder()->getEvent() : null;
    }

    /**
     * @param mixed $obj
     * @return Holder
     * @throws \InvalidArgumentException
     */
    protected function getHolder($obj): Holder {
        if ($obj instanceof Holder) {
            return $obj;
        }
        if ($obj instanceof Field) {
            return $obj->getBaseHolder()->getHolder();
        }
        if ($obj instanceof BaseHolder) {
            return $obj->getHolder();
        }
        throw new \InvalidArgumentException();

    }

}
