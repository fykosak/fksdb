<?php

namespace Events\Semantics;

use Nette\Object;

/**
 * Due to author's laziness there's no class doc (or it's self explaining).
 *
 * @author Michal Koutný <michal@fykos.cz>
 */
class EventWas extends Object {

    use WithEventTrait;

    /**
     * @param $obj
     * @return bool
     */
    public function __invoke($obj) {
        $event = $this->getEvent($obj);
        return $event->begin->getTimestamp() <= time();
    }

    /**
     * @return string
     */
    public function __toString() {
        return 'eventWas';
    }

}
