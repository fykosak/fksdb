<?php

namespace Events\Semantics;

use Events\Machine\Transition;
use Events\Model\Holder\BaseHolder;
use Events\Model\Holder\Field;
use Events\Model\Holder\Holder;
use FKSDB\ORM\Models\ModelEvent;
use Nette\Application\BadRequestException;
use Nette\DeprecatedException;

/**
 * Due to author's laziness there's no class doc (or it's self explaining).
 *
 * @author Michal Koutný <michal@fykos.cz>
 */
trait WithEventTrait {

    /**
     * @param mixed $obj
     * @return ModelEvent
     * @throws BadRequestException
     */
    protected function getEvent($obj): ModelEvent {
        return ($holder = $this->getHolder($obj)) ? $holder->getPrimaryHolder()->getEvent() : null;
    }

    /**
     * @param mixed $obj
     * @return Holder
     * @throws BadRequestException
     */
    protected function getHolder($obj): Holder {
        if ($obj instanceof Holder) {
            return $obj;
        }
        if ($obj instanceof Transition) {
            throw new DeprecatedException();
        }
        if ($obj instanceof Field) {
            return $obj->getBaseHolder()->getHolder();
        }
        if ($obj instanceof BaseHolder) {
            return $obj->getHolder();
        }
        throw new BadRequestException();

    }

}
