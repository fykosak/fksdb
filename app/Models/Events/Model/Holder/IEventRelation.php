<?php

namespace FKSDB\Models\Events\Model\Holder;

use FKSDB\Models\ORM\Models\ModelEvent;

/**
 * Due to author's laziness there's no class doc (or it's self explaining).
 *
 * @author Michal Koutný <michal@fykos.cz>
 */
interface IEventRelation {

    public function getEvent(ModelEvent $event): ModelEvent;
}
