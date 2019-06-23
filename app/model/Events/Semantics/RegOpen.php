<?php

namespace Events\Semantics;

use Nette\SmartObject;

/**
 * Due to author's laziness there's no class doc (or it's self explaining).
 *
 * @author Michal Koutný <michal@fykos.cz>
 */
class RegOpen {
    use SmartObject;
    use WithEventTrait;

    /**
     * @param $obj
     * @return bool
     */
    public function __invoke($obj) {
        $event = $this->getEvent($obj);
        return (!$event->registration_begin || $event->registration_begin->getTimestamp() <= time()) && (!$event->registration_end || $event->registration_end->getTimestamp() >= time());
    }

    /**
     * @return string
     */
    public function __toString() {
        return 'regOpen';
    }

}
