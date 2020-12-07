<?php

namespace FKSDB\Model\Events\Model;

use FKSDB\Model\Events\EventDispatchFactory;
use FKSDB\Model\Logging\ILogger;
use FKSDB\Model\ORM\Models\ModelEvent;
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

    public function __construct(Connection $connection, Container $container, EventDispatchFactory $eventDispatchFactory) {
        $this->connection = $connection;
        $this->container = $container;
        $this->eventDispatchFactory = $eventDispatchFactory;
    }

    public function create(ModelEvent $event, ILogger $logger): ApplicationHandler {
        return new ApplicationHandler($event, $logger, $this->connection, $this->container, $this->eventDispatchFactory);
    }
}
