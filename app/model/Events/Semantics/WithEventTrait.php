<?php

namespace Events\Semantics;

use Events\Machine\Transition;
use Events\Model\Holder\BaseHolder;
use Events\Model\Holder\Field;
use ModelEvent;

/**
 * Due to author's laziness there's no class doc (or it's self explaining).
 * 
 * @author Michal Koutný <michal@fykos.cz>
 */
trait WithEventTrait {

    /**
     * @param mixed $obj
     * @return ModelEvent
     */
    protected function getEvent($obj) {
        return ($holder = $this->getHolder($obj)) ? $holder->getEvent() : null;
    }

    /**
     * @param mixed $obj
     * @return Holder
     */
    protected function getHolder($obj) {
        if ($obj instanceof Transition)
            return $obj->getBaseMachine()->getMachine()->getHolder();

        if ($obj instanceof Field)
            return $obj->getBaseHolder()->getHolder();

        if ($obj instanceof BaseHolder)
            return $obj->getHolder();
    }

}
