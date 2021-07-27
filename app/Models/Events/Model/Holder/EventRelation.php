<?php

namespace FKSDB\Models\Events\Model\Holder;

use FKSDB\Models\ORM\Models\ModelEvent;

interface EventRelation {

    public function getEvent(ModelEvent $event): ModelEvent;
}
