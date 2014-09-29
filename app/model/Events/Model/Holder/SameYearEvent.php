<?php

namespace Events\Model\Holder;

use ModelEvent;
use Nette\InvalidArgumentException;
use ServiceEvent;

/**
 * Due to author's laziness there's no class doc (or it's self explaining).
 * 
 * @author Michal Koutný <michal@fykos.cz>
 */
class SameYearEvent implements IEventRelation {

    private $eventTypeId;

    /**
     * @var ServiceEvent
     */
    private $serviceEvent;

    function __construct($eventTypeId, ServiceEvent $serviceEvent) {
        $this->eventTypeId = $eventTypeId;
        $this->serviceEvent = $serviceEvent;
    }

    public function getEvent(ModelEvent $event) {
        $result = $this->serviceEvent->getTable()->where(array(
            'event_type_id' => $this->eventTypeId,
            'year' => $event->year,
        ));
        $row = $result->fetch();
        if ($row === false) {
            throw new InvalidArgumentException("No event with event_type_id " . $this->eventTypeId . " for the year " . $event->year . ".");
        } else if ($result->fetch() !== false) {
            throw new InvalidArgumentException("Ambiguous events with event_type_id " . $this->eventTypeId . " for the year " . $event->year . ".");
        } else {
            return ModelEvent::createFromTableRow($row);
        }
    }

}
