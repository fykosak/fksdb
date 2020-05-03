<?php

namespace FKSDB\Events\Model\Holder;

use FKSDB\ORM\Models\ModelEvent;

/**
 * Due to author's laziness there's no class doc (or it's self explaining).
 *
 * @author Michal Koutný <michal@fykos.cz>
 */
interface IEventRelation {

    /**
     * @param ModelEvent $event
     * @return ModelEvent
     */
    public function getEvent(ModelEvent $event);
}
