<?php

namespace FKSDB\Events\Model;

use FKSDB\Events\EventDispatchFactory;
use FKSDB\Logging\ILogger;
use FKSDB\ORM\Models\ModelEvent;
use Nette\Database\Connection;
use Nette\DI\Container;

/**
 * Due to author's laziness there's no class doc (or it's self explaining).
 *
 * @author Michal Koutný <michal@fykos.cz>
 */
class ApplicationHandlerFactory {

    private Connection $connection;

    private Container $container;

    private EventDispatchFactory $eventDispatchFactory;

    /**
     * ApplicationHandlerFactory constructor.
     * @param Connection $connection
     * @param Container $container
     * @param EventDispatchFactory $eventDispatchFactory
     */
    public function __construct(Connection $connection, Container $container, EventDispatchFactory $eventDispatchFactory) {
        $this->connection = $connection;
        $this->container = $container;
        $this->eventDispatchFactory = $eventDispatchFactory;
    }

    public function create(ModelEvent $event, ILogger $logger): ApplicationHandler {
        return new ApplicationHandler($event, $logger, $this->connection, $this->container, $this->eventDispatchFactory);
    }
}
