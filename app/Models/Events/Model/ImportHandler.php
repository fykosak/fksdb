<?php

declare(strict_types=1);

namespace FKSDB\Models\Events\Model;

use FKSDB\Models\Events\EventDispatchFactory;
use FKSDB\Models\Events\Exceptions\ConfigurationNotFoundException;
use FKSDB\Models\Events\Model\Holder\BaseHolder;
use FKSDB\Models\ORM\Models\EventModel;
use FKSDB\Models\ORM\Models\EventParticipantModel;
use FKSDB\Models\ORM\Services\EventParticipantService;
use FKSDB\Models\Utils\CSVParser;
use Nette\SmartObject;
use Nette\Utils\ArrayHash;

class ImportHandler
{
    use SmartObject;

    public const STATELESS_IGNORE = 'ignore';
    public const STATELESS_KEEP = 'keep';

    private CSVParser $parser;
    private EventDispatchFactory $eventDispatchFactory;
    private EventParticipantService $eventParticipantService;
    private EventModel $event;

    public function __construct(
        CSVParser $parser,
        EventDispatchFactory $eventDispatchFactory,
        EventParticipantService $eventParticipantService,
        EventModel $event
    ) {
        $this->parser = $parser;
        $this->event = $event;
        $this->eventDispatchFactory = $eventDispatchFactory;
        $this->eventParticipantService = $eventParticipantService;
    }

    /**
     * @throws ImportHandlerException
     * @throws ConfigurationNotFoundException
     * @throws \Throwable
     */
    public function import(ApplicationHandler $handler, string $errorMode, string $stateless): bool
    {
        set_time_limit(0);
        $holdersMap = $this->createHoldersMap();
        $holder = $this->eventDispatchFactory->getDummyHolder($this->event);
        $handler->setErrorMode($errorMode);
        $handler->beginTransaction();
        $hasError = false;
        foreach ($this->parser as $row) {
            $values = ArrayHash::from($this->rowToValues($row));
            $keyValue = $values[$holder->name]['person_id'];
            if (
                !isset($values[$holder->name]['status'])
                || !$values[$holder->name]['status']
            ) {
                if ($stateless == self::STATELESS_IGNORE) {
                    continue;
                } elseif ($stateless == self::STATELESS_KEEP) {
                    unset($values[$holder->name]['status']);
                }
            }
            $holder = $holdersMap[$keyValue] ?? $this->eventDispatchFactory->getDummyHolder($this->event);
            try {
                $handler->store($holder, $values);
            } catch (ApplicationHandlerException $exception) {
                $hasError = true;
                if ($errorMode == ApplicationHandler::ERROR_ROLLBACK) {
                    throw new ImportHandlerException(_('Import failed.'), 0, $exception);
                }
            }
        }
        $handler->commit(true);
        return !$hasError;
    }

    private function prepareColumnName(string $columnName, BaseHolder $holder): array
    {
        $parts = explode('.', $columnName);
        if (count($parts) == 1) {
            return [$holder->name, $parts[0]];
        } else {
            return $parts;
        }
    }

    /**
     * @throws ImportHandlerException
     */
    private function rowToValues(iterable $row): array
    {
        $holder = $this->eventDispatchFactory->getDummyHolder($this->event);
        $values = [];
        $fieldExists = false;
        $fieldNames = array_keys($holder->getFields());
        foreach ($row as $columnName => $value) {
            if (is_numeric($columnName)) { // hack for new PDO
                continue;
            }
            [$baseHolderName, $fieldName] = $this->prepareColumnName($columnName, $holder);

            if (!isset($values[$baseHolderName])) {
                $values[$baseHolderName] = [];
            }
            $values[$baseHolderName][$fieldName] = $value;
            if (in_array($fieldName, $fieldNames)) {
                $fieldExists = true;
            }
        }
        if (!$fieldExists) {
            throw new ImportHandlerException(_('CSV does not contain correct heading.'));
        }
        return $values;
    }

    /**
     * @return BaseHolder[]
     */
    private function createHoldersMap(): array
    {
        $holders = [];
        $machine = $this->eventDispatchFactory->getEventMachine($this->event);
        /** @var EventParticipantModel $model */
        foreach ($this->event->getParticipants() as $model) {
            $holders[$model->person_id] = $machine->createHolder($model);
        }
        return $holders;
    }
}
