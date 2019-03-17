<?php

namespace Events\Model\Grid;

use Events\UndeclaredEventException;
use FKSDB\ORM\Models\ModelEvent;
use FKSDB\ORM\Models\ModelPerson;
use FKSDB\ORM\Tables\TypedTableSelection;
use Nette\DI\Container;

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
     * @var \FKSDB\ORM\Models\ModelPerson
     */
    private $person;

    /**
     * RelatedPersonSource constructor.
     * @param \FKSDB\ORM\Models\ModelPerson $person
     * @param \FKSDB\ORM\Tables\TypedTableSelection $events
     * @param Container $container
     */
    function __construct(ModelPerson $person, TypedTableSelection $events, Container $container) {
        parent::__construct($events, $container);
        $this->person = $person;
    }

    /**
     * @param \FKSDB\ORM\Models\ModelEvent $event
     * @return SingleEventSource|null
     */
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
