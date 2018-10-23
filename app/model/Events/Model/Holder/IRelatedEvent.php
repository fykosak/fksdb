<?php

namespace Events\Model\Holder;

use FKSDB\ORM\ModelEvent;

/**
 * Due to author's laziness there's no class doc (or it's self explaining).
 *
 * @author Michal Koutný <michal@fykos.cz>
 */
interface IEventRelation {

    /**
     * @return \FKSDB\ORM\ModelEvent
     */
    public function getEvent(ModelEvent $event);
}
