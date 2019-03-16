<?php

namespace Events\Model\Holder;

use FKSDB\ORM\Models\ModelEvent;

/**
 * Due to author's laziness there's no class doc (or it's self explaining).
 *
 * @author Michal Koutný <michal@fykos.cz>
 */
interface IEventRelation {

    /**
     * @param \FKSDB\ORM\Models\ModelEvent $event
     * @return \FKSDB\ORM\Models\ModelEvent
     */
    public function getEvent(ModelEvent $event);
}
