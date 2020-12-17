<?php

namespace FKSDB\Model\ORM\Models;

/**
 * Interface IEventReferencedModel
 * *
 */
interface IEventReferencedModel {
    public function getEvent(): ?ModelEvent;
}
