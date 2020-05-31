<?php

namespace FKSDB\Events\Model;

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

    /**
     * ApplicationHandlerFactory constructor.
     * @param Connection $connection
     * @param Container $container
     */
    public function __construct(Connection $connection, Container $container) {
        $this->connection = $connection;
        $this->container = $container;
    }

    public function create(ModelEvent $event, ILogger $logger): ApplicationHandler {
        return new ApplicationHandler($event, $logger, $this->connection, $this->container);
    }

}
