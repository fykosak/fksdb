<?php

namespace Events\Model\Grid;

use FKSDB\ORM\ModelEvent;
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
class InitSource extends AggregatedPersonSource implements IHolderSource {


    function __construct(TypedTableSelection $events, SystemContainer $container) {
        parent::__construct($events, $container);
    }

    public function processEvent(ModelEvent $event) {

        $holder = $this->container->createEventHolder($event);
        $holder->setModel();

        return $holder;
    }

}
