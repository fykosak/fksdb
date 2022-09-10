<?php

declare(strict_types=1);

namespace FKSDB\Models\Events\Model;

use FKSDB\Models\Events\Exceptions\ConfigurationNotFoundException;
use FKSDB\Models\Expressions\NeonSchemaException;
use FKSDB\Models\Events\EventDispatchFactory;
use FKSDB\Models\Events\Model\Grid\SingleEventSource;
use FKSDB\Models\Events\Model\Holder\BaseHolder;
use FKSDB\Models\Utils\CSVParser;
use Nette\DI\MissingServiceException;
use Nette\SmartObject;
use Nette\Utils\ArrayHash;

class ImportHandler
{
    use SmartObject;

    public const STATELESS_IGNORE = 'ignore';
    public const STATELESS_KEEP = 'keep';

    public const KEY_NAME = 'person_id';

    private SingleEventSource $source;
    private CSVParser $parser;
    private EventDispatchFactory $eventDispatchFactory;

    public function __construct(
        CSVParser $parser,
        SingleEventSource $source,
        EventDispatchFactory $eventDispatchFactory
    ) {
        $this->parser = $parser;
        $this->source = $source;
        $this->eventDispatchFactory = $eventDispatchFactory;
    }

    /**
     * @throws ImportHandlerException
     * @throws NeonSchemaException
     * @throws ConfigurationNotFoundException
     * @throws MissingServiceException
     */
    public function import(ApplicationHandler $handler, string $errorMode, string $stateless): bool
    {
        set_time_limit(0);
        $holdersMap = $this->createHoldersMap();
        $holder = $this->source->getDummyHolder();

        $handler->setErrorMode($errorMode);
        $handler->beginTransaction();
        $hasError = false;
        foreach ($this->parser as $row) {
            $values = ArrayHash::from($this->rowToValues($row));
            $keyValue = $values[$holder->name][self::KEY_NAME];
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
            $holder = $holdersMap[$keyValue] ?? $this->eventDispatchFactory->getDummyHolder($this->source->getEvent());
            try {
                $handler->store($holder, $values);
            } catch (ApplicationHandlerException $exception) {
                $hasError = true;
                if ($errorMode == ApplicationHandler::ERROR_ROLLBACK) {
                    throw new ImportHandlerException(_('Import failed.'), null, $exception);
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
        $holder = $this->source->getDummyHolder();
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
     * @throws NeonSchemaException
     */
    private function createHoldersMap(): array
    {
        $primaryBaseHolder = $this->source->getDummyHolder();
        $pkName = $primaryBaseHolder->service->getTable()->getPrimary();

        $result = [];
        foreach ($this->source->getHolders() as $pkValue => $holder) {
            if (self::KEY_NAME == $pkName) {
                $keyValue = $pkValue;
            } else {
                $fields = $holder->getFields();
                $keyValue = $fields[self::KEY_NAME]->getValue();
            }
            $result[$keyValue] = $holder;
        }
        return $result;
    }
}
