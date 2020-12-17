<?php

namespace FKSDB\Model\Events\Semantics;

use FKSDB\Model\Events\Model\Holder\BaseHolder;
use FKSDB\Model\Events\Model\Holder\Field;
use FKSDB\Model\Events\Model\Holder\Holder;
use FKSDB\Model\ORM\Models\ModelEvent;

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
        return $this->getHolder($obj)->getPrimaryHolder()->getEvent();
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
