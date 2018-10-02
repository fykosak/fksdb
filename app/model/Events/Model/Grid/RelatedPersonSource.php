<?php

namespace Events\Model\Grid;

use Events\UndeclaredEventException;
use FKSDB\ORM\ModelEvent;
use FKSDB\ORM\ModelPerson;
use ORM\Tables\TypedTableSelection;
use SystemContainer;

/**
 * Due to author's laziness there's no class doc (or it's self explaining).
 *
 * @author Michal Koutný <michal@fykos.cz>
 *
 * @method SingleEventSource where()
 * @method SingleEventSource order()
 * @method SingleEventSource limit()
 * @method SingleEventSource count()
 */
class RelatedPersonSource extends AggregatedPersonSource implements IHolderSource {

    /**
     * @var ModelPerson
     */
    private $person;

    function __construct(ModelPerson $person, TypedTableSelection $events, SystemContainer $container) {
        parent::__construct($events, $container);
        $this->person = $person;
    }

    public function processEvent(ModelEvent $event) {
        $personId = $this->person->getPrimary();

	try {
            $eventSource = new SingleEventSource($event, $this->container);
	} catch (UndeclaredEventException $e) {
	    return null;
	}


        $subconditions = [];
        $count = 0;

        $primaryPersonIds = $eventSource->getDummyHolder()->getPrimaryHolder()->getPersonIds();
        if ($primaryPersonIds) {
            $subconditions[] = implode(' = ?  OR ', $primaryPersonIds) . ' = ?';
            $count += count($primaryPersonIds);
        }

        foreach ($eventSource->getDummyHolder()->getGroupedSecondaryHolders() as $group) {
            if ($group['personIds']) {
                $subconditions[] = implode(' = ?  OR ', $group['personIds']) . ' = ?';
                $count += count($group['personIds']);
            }
        }

        if ($count == 1) {
            $parameters = $personId;
        } else {
            $parameters = array_fill(0, $count, $personId);
        }
        $eventSource->where(implode(' OR ', $subconditions), $parameters);

        return $eventSource;
    }

}
