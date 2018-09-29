<?php

namespace Events\Model\Holder;

use ModelEvent;

/**
 * Due to author's laziness there's no class doc (or it's self explaining).
 *
 * @author Michal Koutný <michal@fykos.cz>
 */
interface IEventRelation {

    /**
     * @return ModelEvent
     * @param ModelEvent $event
     */
    public function getEvent(ModelEvent $event);
}
