<?php

namespace FKSDB\Events\Model\Grid;

use FKSDB\Config\NeonSchemaException;
use FKSDB\Events\EventDispatchFactory;
use FKSDB\ORM\Models\ModelEvent;
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
class InitSource extends AggregatedPersonSource implements IHolderSource {

    /**
     * InitSource constructor.
     * @param TypedTableSelection $events
     * @param Container $container
     */
    public function __construct(TypedTableSelection $events, Container $container) {
        parent::__construct($events, $container);
    }

    /**
     * @param ModelEvent $event
     * @return Holder
     * @throws BadRequestException
     * @throws NeonSchemaException
     */
    public function processEvent(ModelEvent $event) {
        /** @var EventDispatchFactory $factory */
        $factory = $this->container->getByType(EventDispatchFactory::class);
        $holder = $factory->getDummyHolder($event);
        $holder->setModel();
        return $holder;
    }

}
