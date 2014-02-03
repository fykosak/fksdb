<?php

namespace Events\Semantics;

use Nette\Object;

/**
 * Due to author's laziness there's no class doc (or it's self explaining).
 * 
 * @author Michal Koutný <michal@fykos.cz>
 */
class RegOpen extends Object {
    use WithEventTrait;

    public function __invoke($obj) {
        $event = $this->getEvent($obj);
        return (!$event->registration_begin || $event->registration_begin->getTimestamp() <= time()) && (!$event->registration_end || $event->registration_end->getTimestamp() >= time());
    }

}
