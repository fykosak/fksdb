<?php

namespace Events\Semantics;

use FKSDB\Expressions\EvaluatedExpression;
use Nette\Application\BadRequestException;
use Nette\SmartObject;

/**
 * Due to author's laziness there's no class doc (or it's self explaining).
 *
 * @author Michal Koutný <michal@fykos.cz>
 */
class EventWas extends EvaluatedExpression {
    use SmartObject;
    use WithEventTrait;

    /**
     * @param array $args
     * @return bool
     * @throws BadRequestException
     */
    public function __invoke(...$args): bool {
        $event = $this->getEvent($args[0]);
        return $event->begin->getTimestamp() <= time();
    }

    /**
     * @return string
     */
    public function __toString() {
        return 'eventWas';
    }

}
