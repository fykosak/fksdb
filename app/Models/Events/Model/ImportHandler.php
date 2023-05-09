<?php

declare(strict_types=1);

namespace FKSDB\Models\Events\Model;

use FKSDB\Models\Events\Exceptions\ConfigurationNotFoundException;
use FKSDB\Models\ORM\Models\EventModel;
use FKSDB\Models\ORM\Services\EventParticipantService;
use FKSDB\Models\Utils\CSVParser;
use Nette\Database\Connection;
use Nette\DI\Container;
use Nette\SmartObject;

class ImportHandler
{
    use SmartObject;

    private EventParticipantService $eventParticipantService;
    private Connection $connection;

    public function __construct(Container $container)
    {
        $container->callInjects($this);
    }

    public function inject(EventParticipantService $eventParticipantService, Connection $connection): void
    {
        $this->eventParticipantService = $eventParticipantService;
        $this->connection = $connection;
    }

    /**
     * @throws ImportHandlerException
     * @throws ConfigurationNotFoundException
     * @throws \Throwable
     */
    public function __invoke(CSVParser $parser, EventModel $event): void
    {
        $this->connection->beginTransaction();
        try {
            foreach ($parser as $row) {
                $values = [];
                foreach ($row as $columnName => $value) {
                    $value[$columnName] = $value;
                }
                $values['event_id'] = $event->event_id;
                $this->eventParticipantService->storeModel($values);
            }
        } catch (\Throwable $exception) {
            $this->connection->rollBack();
            throw new ImportHandlerException(_('Import failed.'), 0, $exception);
        }
        $this->connection->commit();
    }
}
