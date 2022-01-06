<?php

declare(strict_types=1);

namespace FKSDB\Models\Events\Semantics;

use FKSDB\Models\Events\Model\Holder\BaseHolder;
use FKSDB\Models\Events\Model\Holder\Field;
use FKSDB\Models\Events\Model\Holder\Holder;
use FKSDB\Models\ORM\Models\ModelEvent;

trait WithEventTrait {

    /**
     * @throws \InvalidArgumentException
     */
    protected function getEvent(object $obj): ModelEvent {
        return $this->getHolder($obj)->getPrimaryHolder()->getEvent();
    }

    /**
     * @throws \InvalidArgumentException
     */
    protected function getHolder(object $obj): Holder {
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
