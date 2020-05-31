<?php

namespace FKSDB\Events\Model\Grid;

use FKSDB\Config\NeonSchemaException;
use FKSDB\Events\UndeclaredEventException;
use FKSDB\ORM\Models\ModelEvent;
use FKSDB\ORM\Models\ModelPerson;
use FKSDB\ORM\Tables\TypedTableSelection;
use Nette\Application\BadRequestException;
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

    private ModelPerson $person;

    /**
     * RelatedPersonSource constructor.
     * @param ModelPerson $person
     * @param TypedTableSelection $events
     * @param Container $container
     */
    public function __construct(ModelPerson $person, TypedTableSelection $events, Container $container) {
        parent::__construct($events, $container);
        $this->person = $person;
    }

    /**
     * @param ModelEvent $event
     * @return SingleEventSource|null
     * @throws NeonSchemaException
     * @throws BadRequestException
     */
    public function processEvent(ModelEvent $event): ?SingleEventSource {
        $personId = $this->person->getPrimary();

        try {
            $eventSource = new SingleEventSource($event, $this->container);
        } catch (UndeclaredEventException $exception) {
            return null;
        }


        $subConditions = [];
        $count = 0;

        $primaryPersonIds = $eventSource->getDummyHolder()->getPrimaryHolder()->getPersonIds();
        if ($primaryPersonIds) {
            $subConditions[] = implode(' = ?  OR ', $primaryPersonIds) . ' = ?';
            $count += count($primaryPersonIds);
        }

        foreach ($eventSource->getDummyHolder()->getGroupedSecondaryHolders() as $group) {
            if ($group['personIds']) {
                $subConditions[] = implode(' = ?  OR ', $group['personIds']) . ' = ?';
                $count += count($group['personIds']);
            }
        }

        if ($count == 1) {
            $parameters = $personId;
        } else {
            $parameters = array_fill(0, $count, $personId);
        }
        $eventSource->where(implode(' OR ', $subConditions), $parameters);

        return $eventSource;
    }

}
