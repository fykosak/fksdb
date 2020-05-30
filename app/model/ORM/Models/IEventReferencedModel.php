<?php

namespace FKSDB\ORM\Models;

/**
 * Interface IEventReferencedModel
 * *
 */
interface IEventReferencedModel {
    public function getEvent(): ModelEvent;
}
