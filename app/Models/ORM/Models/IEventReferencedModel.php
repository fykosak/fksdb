<?php

namespace FKSDB\Models\ORM\Models;

/**
 * Interface IEventReferencedModel
 * *
 */
interface IEventReferencedModel {
    public function getEvent(): ?ModelEvent;
}
