<?php

namespace FKSDB\Events\Model;

use FKSDB\Config\NeonSchemaException;
use FKSDB\Events\EventDispatchFactory;
use FKSDB\Events\Model\Grid\SingleEventSource;
use FKSDB\Events\Model\Holder\BaseHolder;
use FKSDB\Events\Model\Holder\Holder;
use FKSDB\Utils\CSVParser;
use Nette\DI\Container;
use Nette\SmartObject;
use Nette\Utils\ArrayHash;

/**
 * Due to author's laziness there's no class doc (or it's self explaining).
 *
 * @author Michal Koutný <michal@fykos.cz>
 */
class ImportHandler {

    use SmartObject;

    public const STATELESS_IGNORE = 'ignore';
    public const STATELESS_KEEP = 'keep';

    public const KEY_NAME = 'person_id';

    private Container $container;

    private SingleEventSource $source;

    private CSVParser $parser;

    public function __construct(Container $container, CSVParser $parser, SingleEventSource $source) {
        $this->container = $container;
        $this->parser = $parser;
        $this->source = $source;
    }

    /**
     * @param ApplicationHandler $handler
     * @param string $errorMode
     * @param string $stateless
     * @return bool
     * @throws NeonSchemaException
     */
    public function import(ApplicationHandler $handler, string $errorMode, string $stateless): bool {
        set_time_limit(0);
        $holdersMap = $this->createHoldersMap();
        $primaryBaseHolder = $this->source->getDummyHolder()->getPrimaryHolder();
        $baseHolderName = $primaryBaseHolder->getName();

        $handler->setErrorMode($errorMode);
        $handler->beginTransaction();
        $hasError = false;
        foreach ($this->parser as $row) {
            $values = ArrayHash::from($this->rowToValues($row));
            $keyValue = $values[$baseHolderName][self::KEY_NAME];
            if (!isset($values[$baseHolderName][BaseHolder::STATE_COLUMN]) || !$values[$baseHolderName][BaseHolder::STATE_COLUMN]) {
                if ($stateless == self::STATELESS_IGNORE) {
                    continue;
                } elseif ($stateless == self::STATELESS_KEEP) {
                    unset($values[$baseHolderName][BaseHolder::STATE_COLUMN]);
                }
            }
            /** @var EventDispatchFactory $factory */
            $factory = $this->container->getByType(EventDispatchFactory::class);
            $holder = isset($holdersMap[$keyValue]) ? $holdersMap[$keyValue] : $factory->getDummyHolder($this->source->getEvent());
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

    private function prepareColumnName(string $columnName, BaseHolder $baseHolder): array {
        $parts = explode('.', $columnName);
        if (count($parts) == 1) {
            return [$baseHolder->getName(), $parts[0]];
        } else {
            return $parts;
        }
    }

    /**
     * @param mixed $row
     * @return array
     */
    private function rowToValues($row): array {
        $primaryBaseHolder = $this->source->getDummyHolder()->getPrimaryHolder();
        $values = [];
        $fieldExists = false;
        $fieldNames = array_keys($primaryBaseHolder->getFields());
        foreach ($row as $columnName => $value) {
            if (is_numeric($columnName)) { // hack for new PDO
                continue;
            }
            [$baseHolderName, $fieldName] = $this->prepareColumnName($columnName, $primaryBaseHolder);

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
     * @return Holder[]
     * @throws NeonSchemaException
     */
    private function createHoldersMap(): array {
        $primaryBaseHolder = $this->source->getDummyHolder()->getPrimaryHolder();
        $pkName = $primaryBaseHolder->getService()->getTable()->getPrimary();

        $result = [];
        foreach ($this->source->getHolders() as $pkValue => $holder) {
            if (self::KEY_NAME == $pkName) {
                $keyValue = $pkValue;
            } else {
                $fields = $holder->getPrimaryHolder()->getFields();
                $keyValue = $fields[self::KEY_NAME]->getValue();
            }
            $result[$keyValue] = $holder;
        }
        return $result;
    }
}
