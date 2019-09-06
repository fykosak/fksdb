<?php

namespace FKSDB\ORM\Models;

/**
 * Interface IEventReferencedModel
 * @package FKSDB\Transitions
 */
interface IEventReferencedModel {

    /**
     * @return ModelEvent
     */
    public function getEvent(): ModelEvent;
}
