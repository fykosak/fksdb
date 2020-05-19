<?php

namespace FKSDB\ORM\Models;

/**
 * Interface IEventReferencedModel
 * @package FKSDB\Transitions
 */
interface IEventReferencedModel {
    public function getEvent(): ModelEvent;
}
